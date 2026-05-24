import { Injectable } from '@angular/core';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { BehaviorSubject, Observable, tap } from 'rxjs';

export interface Article {
  id: string;
  title: string;
  body: string;
  category: string;
  lat: number;
  lng: number;
  authorName: string;
  createdAt: Date;
  url: string;
  source: string;
  status: string;
  verification_status: string;
}

@Injectable({ providedIn: 'root' })
export class ArticleService {

  private apiUrl = 'http://localhost/API_Systems_Nirvana_Yan/flashpoint-api/core/articles.php';
  private articles$ = new BehaviorSubject<Article[]>([]);

  constructor(private http: HttpClient) {
    this.fetchArticles();
  }

  // ─── FETCH FROM API ────────────────────────────────────────────────────────
  fetchArticles(): void {
    this.http.get<any>(`${this.apiUrl}?action=list`).subscribe({
      next: (res) => {
        const mapped = (res.articles ?? []).map((a: any) => ({
          id:                  String(a.id),
          title:               a.title,
          body:                a.body,
          category:            a.category,
          lat:                 parseFloat(a.lat),
          lng:                 parseFloat(a.lng),
          authorName:          a.author_display ?? a.author_name ?? 'Unknown',
          createdAt:           new Date(a.created_at),
          url:                 a.url ?? '',
          source:              a.source ?? '',
          status:              a.status ?? 'pending',
          verification_status: a.verification_status ?? 'unverified',
        }));
        this.articles$.next(mapped);
      },
      error: (err) => console.error('Failed to fetch articles:', err)
    });
  }

  getArticles(): Observable<Article[]> {
    return this.articles$.asObservable();
  }

  getById(id: string): Article | undefined {
    return this.articles$.getValue().find(a => a.id === id);
  }

  // ─── ADD LOCALLY (optimistic) ──────────────────────────────────────────────
  addArticle(data: Omit<Article, 'id' | 'createdAt'>): Article {
    const article: Article = {
      ...data,
      id: crypto.randomUUID(),
      createdAt: new Date(),
    };
    this.articles$.next([...this.articles$.getValue(), article]);
    return article;
  }

  // ─── SUBMIT TO API ─────────────────────────────────────────────────────────
  submitArticle(data: any, token: string): Observable<any> {
    const headers = new HttpHeaders({
      'Authorization': 'Bearer ' + token,
      'Content-Type': 'application/json'
    });
    return this.http.post<any>(`${this.apiUrl}?action=create`, data, { headers }).pipe(
      tap(() => this.fetchArticles()) // refresh list after create
    );
  }
}