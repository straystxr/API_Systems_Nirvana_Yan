import { Component, OnInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { firstValueFrom } from 'rxjs';
import {
  IonHeader, IonToolbar, IonTitle, IonContent, IonIcon,
  IonRefresher, IonRefresherContent
} from '@ionic/angular/standalone';
import { addIcons } from 'ionicons';
import {
  bookmarkOutline, trashOutline, newspaperOutline,
  locationOutline, timeOutline, alertCircleOutline
} from 'ionicons/icons';
import { Auth } from '../services/auth';

@Component({
  selector: 'app-tab2',
  templateUrl: 'tab2.page.html',
  styleUrls: ['tab2.page.scss'],
  standalone: true,
  imports: [
    CommonModule,
    IonHeader, IonToolbar, IonTitle, IonContent,
    IonIcon, IonRefresher, IonRefresherContent
  ]
})
export class Tab2Page implements OnInit {

  private apiUrl = 'http://localhost/API_Systems_Nirvana_Yan/flashpoint-api/core';

  public bookmarks: any[] = [];
  public loading: boolean = true;
  public errorMessage: string = '';
  public successMessage: string = '';
  public removingId: number | null = null;

  constructor(
    private authService: Auth,
    private http: HttpClient
  ) {
    addIcons({
      bookmarkOutline, trashOutline, newspaperOutline,
      locationOutline, timeOutline, alertCircleOutline
    });
  }

  ngOnInit() {
    this.loadBookmarks();
  }

  // ─── LOAD BOOKMARKS ──────────────────────────────────────────────────────
  async loadBookmarks(event?: any) {
    this.loading = true;
    this.errorMessage = '';

    try {
      const headers = new HttpHeaders({
        'Authorization': 'Bearer ' + this.authService.getToken()
      });

      const res: any = await firstValueFrom(
        this.http.get(`${this.apiUrl}/bookmarks.php?action=list`, { headers })
      );

      this.bookmarks = res.bookmarks ?? [];

    } catch (e: any) {
      this.errorMessage = e?.error?.error || 'Could not load bookmarks. Please try again.';
    } finally {
      this.loading = false;
      if (event) event.target.complete();
    }
  }

  // ─── REMOVE BOOKMARK ─────────────────────────────────────────────────────
  async removeBookmark(articleId: number) {
    this.removingId = articleId;
    this.successMessage = '';
    this.errorMessage = '';

    try {
      const headers = new HttpHeaders({
        'Authorization': 'Bearer ' + this.authService.getToken(),
        'Content-Type': 'application/json'
      });

      await firstValueFrom(
        this.http.delete(`${this.apiUrl}/bookmarks.php?action=remove`,
          { headers, body: { article_id: articleId } }
        )
      );

      this.bookmarks = this.bookmarks.filter(b => b.id !== articleId);
      this.successMessage = 'Bookmark removed successfully.';
      setTimeout(() => this.successMessage = '', 3000);

    } catch (e: any) {
      this.errorMessage = e?.error?.error || 'Could not remove bookmark.';
    } finally {
      this.removingId = null;
    }
  }

  // ─── HELPERS ─────────────────────────────────────────────────────────────
  formatDate(dateStr: string): string {
    if (!dateStr) return '';
    const d = new Date(dateStr);
    return d.toLocaleDateString('en-GB', { day: 'numeric', month: 'short', year: 'numeric' });
  }

  getCategoryColor(category: string): string {
    const map: any = {
      news: '#1565C0',
      politics: '#6A1B9A',
      sport: '#2E7D32',
      crime: '#C0392B',
      environment: '#00695C',
    };
    return map[category?.toLowerCase()] || '#1a2d5a';
  }
}