import { AfterViewInit, Component, OnDestroy, inject } from '@angular/core';
import { CommonModule } from '@angular/common';
import {
  IonHeader, IonToolbar, IonTitle, IonContent,
  ModalController, ToastController
} from '@ionic/angular/standalone';
import { ArticleModalComponent } from '../components/article-modal/article-modal.component';
import { ArticleService, Article } from '../services/article';
import { Auth } from '../services/auth';
import { Subscription } from 'rxjs';
import * as L from 'leaflet';

@Component({
  selector: 'app-tab1',
  templateUrl: 'tab1.page.html',
  styleUrls: ['tab1.page.scss'],
  standalone: true,
  imports: [CommonModule, IonHeader, IonToolbar, IonTitle, IonContent]
})
export class Tab1Page implements AfterViewInit, OnDestroy {

  map!: L.Map;
  private markers = new Map<string, L.Marker>();
  private articleSub!: Subscription;
  private modalCtrl = inject(ModalController);
  private toastCtrl = inject(ToastController);

  // Sidebar state
  public sidebarOpen: boolean = false;
  public articles: Article[] = [];
  public selectedArticle: Article | null = null;
  public hoveredId: string | null = null;

  constructor(
    private articleService: ArticleService,
    private authService: Auth
  ) {}

  ngAfterViewInit() {
    this.initMap();
  }

  ionViewDidEnter() {
    setTimeout(() => this.map.invalidateSize(), 200);
    // Refresh articles from API every time tab is visited
    this.articleService.fetchArticles();
  }

  ngOnDestroy() {
    if (this.articleSub) this.articleSub.unsubscribe();
  }

  // ─── MAP INIT ──────────────────────────────────────────────────────────────
  private initMap(): void {
    this.map = L.map('map', {
      center: [35.9375, 14.3754],
      zoom: 11,
      renderer: L.canvas(),
      zoomControl: false,
      minZoom: 10,
      maxZoom: 20,
    });

    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png', {
      attribution: '',
      subdomains: 'abcd',
      maxZoom: 20,
    }).addTo(this.map);

    L.control.zoom({ position: 'topright' }).addTo(this.map);
    this.enablePinDrop();
    this.watchArticles();
  }

  // ─── PIN DROP ON MAP CLICK ─────────────────────────────────────────────────
  private enablePinDrop(): void {
    this.map.on('click', async (e: L.LeafletMouseEvent) => {
      // Close sidebar if open when tapping map
      this.sidebarOpen = false;
      this.selectedArticle = null;
      await this.openArticleModal(e.latlng.lat, e.latlng.lng);
    });
  }

  private async openArticleModal(lat: number, lng: number): Promise<void> {
    const modal = await this.modalCtrl.create({
      component: ArticleModalComponent,
      componentProps: { lat, lng },
      breakpoints: [0, 0.92],
      initialBreakpoint: 0.92,
    });

    await modal.present();
    const { data, role } = await modal.onWillDismiss();

    if (role === 'confirm' && data) {
      const token = this.authService.getToken() ?? '';
      this.articleService.submitArticle({
        title:    data.title,
        body:     data.body,
        category: data.category,
        lat, lng,
        source:   data.authorName,
        url:      '',
      }, token).subscribe({
        next: async () => {
          await this.showToast(`"${data.title}" submitted for review!`);
        },
        error: async (err) => {
          const msg = err?.error?.error ?? 'Failed to submit article';
          await this.showToast(msg, 'danger');
        }
      });
    }
  }

  // ─── WATCH ARTICLES FROM API ───────────────────────────────────────────────
  private watchArticles(): void {
    this.articleSub = this.articleService.getArticles().subscribe(articles => {
      this.articles = articles;

      // Add new markers, keep existing ones
      articles.forEach(article => {
        if (!isNaN(article.lat) && !isNaN(article.lng) && !this.markers.has(article.id)) {
          this.addMarker(article);
        }
      });

      // Remove markers for articles no longer in list
      this.markers.forEach((marker, id) => {
        if (!articles.find(a => a.id === id)) {
          marker.remove();
          this.markers.delete(id);
        }
      });
    });
  }

  // ─── ADD MAP MARKER ────────────────────────────────────────────────────────
  private addMarker(article: Article): void {
    const categoryColors: any = {
      news:       '#1565C0',
      politics:   '#6A1B9A',
      sport:      '#2E7D32',
      crime:      '#C0392B',
      environment:'#00695C',
      culture:    '#E65100',
    };
    const color = categoryColors[article.category?.toLowerCase()] ?? '#E24B4A';

    const icon = L.divIcon({
      className: '',
      html: `
        <div style="
          width:36px;height:36px;
          background:${color};
          border:3px solid white;
          border-radius:50% 50% 50% 0;
          transform:rotate(-45deg);
          box-shadow:0 2px 6px rgba(0,0,0,0.3);">
        </div>`,
      iconSize: [36, 36],
      iconAnchor: [18, 36],
      popupAnchor: [0, -36],
    });

    const marker = L.marker([article.lat, article.lng], { icon }).addTo(this.map);

    marker.bindPopup(`
      <div style="min-width:180px;font-family:sans-serif;padding:4px">
        <p style="margin:0 0 4px;font-size:11px;color:#888;text-transform:capitalize">${article.category}</p>
        <strong style="font-size:14px;color:#1a2d5a">${article.title}</strong>
        <p style="margin:6px 0 4px;font-size:12px;color:#555">By ${article.authorName}</p>
        <p style="margin:0;font-size:12px;color:#333">${article.body.substring(0, 100)}${article.body.length > 100 ? '...' : ''}</p>
        <p style="margin:6px 0 0;font-size:11px;color:#f5a623;font-weight:700;text-transform:capitalize">${article.verification_status}</p>
      </div>
    `);

    // Clicking marker opens full article in sidebar
    marker.on('click', () => {
      this.selectedArticle = article;
      this.sidebarOpen = true;
    });

    this.markers.set(article.id, marker);
  }

  // ─── SIDEBAR CONTROLS ──────────────────────────────────────────────────────
  toggleSidebar() {
    this.sidebarOpen = !this.sidebarOpen;
    if (!this.sidebarOpen) this.selectedArticle = null;
  }

  openArticleDetail(article: Article) {
    this.selectedArticle = article;
    // Fly map to article pin
    if (!isNaN(article.lat) && !isNaN(article.lng)) {
      this.map.flyTo([article.lat, article.lng], 14, { duration: 0.8 });
      this.markers.get(article.id)?.openPopup();
    }
  }

  closeSidebar() {
    this.sidebarOpen = false;
    this.selectedArticle = null;
  }

  onArticleHover(article: Article) {
    this.hoveredId = article.id;
    if (!isNaN(article.lat) && !isNaN(article.lng)) {
      this.markers.get(article.id)?.openPopup();
    }
  }

  onArticleLeave() {
    this.hoveredId = null;
  }

  getCategoryColor(category: string): string {
    const map: any = {
      news: '#1565C0', politics: '#6A1B9A',
      sport: '#2E7D32', crime: '#C0392B',
      environment: '#00695C', culture: '#E65100',
    };
    return map[category?.toLowerCase()] ?? '#1a2d5a';
  }

  private async showToast(message: string, color = 'success'): Promise<void> {
    const toast = await this.toastCtrl.create({ message, duration: 3000, position: 'top', color });
    await toast.present();
  }
}