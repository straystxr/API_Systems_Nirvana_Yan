import { Injectable } from '@angular/core';
import { HttpClient } from '@angular/common/http';
import { firstValueFrom } from 'rxjs';

@Injectable({
  providedIn: 'root',
})
export class Auth {

  // Your PHP API base URL
  private apiUrl = 'http://localhost/API_Systems_Nirvana_Yan/flashpoint-api/api';

  constructor(private http: HttpClient) {}

  // ─── LOGIN ────────────────────────────────────────────────────────────────
  async login(username: string, password: string): Promise<any> {
    try {
      const response: any = await firstValueFrom(
        this.http.post(`${this.apiUrl}/auth/login`, {
          username,
          password
        })
      );

      // Save token and user to localStorage
      localStorage.setItem('access_token', response.access_token);
      localStorage.setItem('user', JSON.stringify(response.user));

      return response;

    } catch (error: any) {
      const msg = error?.error?.error || 'Login failed. Please try again.';
      throw new Error(msg);
    }
  }

  // ─── REGISTER ─────────────────────────────────────────────────────────────
  async register(name: string, username: string, email: string, password: string, role: string = 'general'): Promise<any> {
    try {
      const response: any = await firstValueFrom(
        this.http.post(`${this.apiUrl}/auth/register`, {
          name,
          username,
          email,
          password,
          role
        })
      );
      return response;
    } catch (error: any) {
      const msg = error?.error?.error || 'Registration failed. Please try again.';
      throw new Error(msg);
    }
  }

  // ─── LOGOUT ───────────────────────────────────────────────────────────────
  logout(): void {
    localStorage.removeItem('access_token');
    localStorage.removeItem('user');
  }

  // ─── HELPERS ──────────────────────────────────────────────────────────────
  getToken(): string | null {
    return localStorage.getItem('access_token');
  }

  getUser(): any {
    const user = localStorage.getItem('user');
    return user ? JSON.parse(user) : null;
  }

  isLoggedIn(): boolean {
    return !!this.getToken();
  }

  // ─── NOT NEEDED (OAuth external flow — leave empty for now) ───────────────
  async handleLoginCallback(url: string): Promise<void> {}
}