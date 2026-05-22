import { Component, OnInit, OnDestroy, ViewChild, ElementRef, AfterViewInit } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { IonContent, IonIcon, ToastController } from '@ionic/angular/standalone';
import { addIcons } from 'ionicons';
import {
  volumeHighOutline, volumeMuteOutline, heartOutline, heart,
  chatbubbleOutline, bookmarkOutline, shareOutline,
  addCircleOutline, closeOutline, cloudUploadOutline
} from 'ionicons/icons';

interface VideoItem {
  id: number;
  url: string;
  article_title: string;
  article_body: string;
  article_category: string;
  verification_status: string;
  uploader_name: string;
  uploader_username: string;
  uploaded_at: string;
  liked?: boolean;
  bookmarked?: boolean;
}

@Component({
  selector: 'app-tab4',
  templateUrl: 'tab4.page.html',
  styleUrls: ['tab4.page.scss'],
  standalone: true,
  imports: [CommonModule, FormsModule, IonContent, IonIcon],
})
export class Tab4Page implements OnInit, AfterViewInit, OnDestroy {

  private mediaUrl   = 'http://localhost/API_Systems_Nirvana_Yan/flashpoint-api/core/media.php';
  private uploadUrl  = 'http://localhost/API_Systems_Nirvana_Yan/flashpoint-api/core/upload.php';

  public videos: VideoItem[] = [];
  public loading = true;
  public muted   = false;

  // Upload modal
  public showUploadModal = false;
  public uploading       = false;
  public uploadError     = '';
  public uploadSuccess   = '';
  public selectedFile: File | null = null;
  public previewUrl: string | null = null;
  public articleId       = '';

  private observer!: IntersectionObserver;

  @ViewChild('fileInput') fileInput!: ElementRef<HTMLInputElement>;

  constructor(
    private http: HttpClient,
    private toastCtrl: ToastController
  ) {
    addIcons({
      volumeHighOutline, volumeMuteOutline, heartOutline, heart,
      chatbubbleOutline, bookmarkOutline, shareOutline,
      addCircleOutline, closeOutline, cloudUploadOutline
    });
  }

  ngOnInit()       { this.loadVideos(); }
  ionViewDidEnter(){ this.setupObserver(); }
  ionViewWillLeave(){ this.pauseAll(); }
  ngAfterViewInit(){ this.setupObserver(); }
  ngOnDestroy()    { this.observer?.disconnect(); }

  // ─── LOAD VIDEOS ───────────────────────────────────────────────────────────
  loadVideos(): void {
    this.http.get<any>(`${this.mediaUrl}?action=videos`).subscribe({
      next: (res) => {
        this.loading = false;
        this.videos  = (res.videos ?? []).map((v: any) => ({ ...v, liked: false, bookmarked: false }));
        setTimeout(() => this.setupObserver(), 400);
      },
      error: () => { this.loading = false; }
    });
  }

  // ─── INTERSECTION OBSERVER — auto play/pause ───────────────────────────────
  private setupObserver(): void {
    this.observer?.disconnect();

    this.observer = new IntersectionObserver((entries) => {
      entries.forEach(entry => {
        const video = entry.target as HTMLVideoElement;
        if (entry.isIntersecting && entry.intersectionRatio >= 0.8) {
          video.play().catch(() => {});
        } else {
          video.pause();
          video.currentTime = 0;
        }
      });
    }, { threshold: 0.8 });

    // Observe all video elements
    setTimeout(() => {
      document.querySelectorAll('.video-player').forEach(v => this.observer.observe(v));
    }, 200);
  }

  private pauseAll(): void {
    document.querySelectorAll<HTMLVideoElement>('.video-player').forEach(v => v.pause());
  }

  // ─── CONTROLS ──────────────────────────────────────────────────────────────
  toggleMute(): void {
    this.muted = !this.muted;
    document.querySelectorAll<HTMLVideoElement>('.video-player').forEach(v => {
      v.muted = this.muted;
    });
  }

  togglePlayPause(event: Event): void {
    const video = (event.currentTarget as HTMLElement).querySelector('video') as HTMLVideoElement;
    if (!video) return;
    video.paused ? video.play() : video.pause();
  }

  toggleLike(video: VideoItem)     { video.liked     = !video.liked; }
  toggleBookmark(video: VideoItem) { video.bookmarked = !video.bookmarked; }

  // ─── UPLOAD MODAL ──────────────────────────────────────────────────────────
  openUpload()  { this.showUploadModal = true; this.resetUpload(); }
  closeUpload() { this.showUploadModal = false; this.resetUpload(); }

  private resetUpload(): void {
    this.selectedFile  = null;
    this.previewUrl    = null;
    this.uploadError   = '';
    this.uploadSuccess = '';
    this.articleId     = '';
    this.uploading     = false;
  }

  pickFile(): void { this.fileInput.nativeElement.click(); }

  onFileSelected(event: Event): void {
    const input = event.target as HTMLInputElement;
    const file  = input.files?.[0];
    if (!file) return;

    // Validate type
    const allowed = ['video/mp4', 'video/webm', 'video/ogg'];
    if (!allowed.includes(file.type)) {
      this.uploadError = 'Please select an MP4, WebM or OGG video file.';
      return;
    }

    this.selectedFile = file;
    this.uploadError  = '';

    // Preview
    const reader = new FileReader();
    reader.onload = (e) => { this.previewUrl = e.target?.result as string; };
    reader.readAsDataURL(file);
  }

  async uploadVideo(): Promise<void> {
    if (!this.selectedFile) { this.uploadError = 'Please select a video first.'; return; }

    this.uploading    = true;
    this.uploadError  = '';
    this.uploadSuccess = '';

    const token = localStorage.getItem('access_token') ?? '';
    const form  = new FormData();
    form.append('media', this.selectedFile);
    if (this.articleId) form.append('article_id', this.articleId);

    const headers = new HttpHeaders({ 'Authorization': 'Bearer ' + token });

    this.http.post<any>(this.uploadUrl, form, { headers }).subscribe({
      next: async (res) => {
        this.uploading     = false;
        this.uploadSuccess = 'Video uploaded successfully!';
        // Add new video to feed immediately
        this.videos.unshift({
          id:                  res.media_id,
          url:                 res.url,
          article_title:       'New Video',
          article_body:        '',
          article_category:    'news',
          verification_status: 'unverified',
          uploader_name:       '',
          uploader_username:   '',
          uploaded_at:         new Date().toISOString(),
          liked:               false,
          bookmarked:          false,
        });
        setTimeout(() => { this.setupObserver(); this.closeUpload(); }, 1500);
        await this.showToast('Video uploaded!');
      },
      error: (err) => {
        this.uploading   = false;
        this.uploadError = err?.error?.error ?? 'Upload failed. Please try again.';
      }
    });
  }

  // ─── HELPERS ───────────────────────────────────────────────────────────────
  getVerificationColor(status: string): string {
    const m: any = { verified: '#5C2D91', in_progress: '#F5A623', unverified: '#E24B4A' };
    return m[status] ?? '#E24B4A';
  }
  getVerificationLabel(status: string): string {
    const m: any = { verified: 'VERIFIED', in_progress: 'IN PROGRESS', unverified: 'UNVERIFIED' };
    return m[status] ?? 'UNVERIFIED';
  }
  getCategoryColor(category: string): string {
    const m: any = { news:'#1565C0', politics:'#6A1B9A', sport:'#2E7D32', crime:'#C0392B', environment:'#00695C', culture:'#E65100' };
    return m[category?.toLowerCase()] ?? '#1a2d5a';
  }

  private async showToast(message: string, color = 'success'): Promise<void> {
    const t = await this.toastCtrl.create({ message, duration: 2500, position: 'top', color });
    await t.present();
  }
}