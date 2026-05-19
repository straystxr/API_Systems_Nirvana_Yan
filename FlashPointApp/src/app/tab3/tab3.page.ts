import { Component, OnInit, ViewChild, ElementRef } from '@angular/core';
import { CommonModule } from '@angular/common';
import { FormsModule } from '@angular/forms';
import { Router } from '@angular/router';
import { HttpClient, HttpHeaders } from '@angular/common/http';
import { firstValueFrom } from 'rxjs';
import { IonHeader, IonToolbar, IonTitle, IonContent, IonIcon } from '@ionic/angular/standalone';
import { addIcons } from 'ionicons';
import {
  personCircleOutline, mailOutline, shieldCheckmarkOutline,
  logOutOutline, cameraOutline, arrowUpCircleOutline,
  arrowDownCircleOutline, closeCircleOutline,
  checkmarkCircle, closeCircle, cardOutline, lockClosedOutline
} from 'ionicons/icons';
import { Auth } from '../services/auth';

@Component({
  selector: 'app-tab3',
  templateUrl: './tab3.page.html',
  styleUrls: ['./tab3.page.scss'],
  standalone: true,
  imports: [CommonModule, FormsModule, IonHeader, IonToolbar, IonTitle, IonContent, IonIcon]
})
export class Tab3Page implements OnInit {
  @ViewChild('photoInput') photoInput!: ElementRef;

  private apiUrl = 'http://localhost/API_Systems_Nirvana_Vella/flashpoint-api/api';

  // Profile
  public user: any = null;
  public membership: any = null;
  public errorMessage: string = '';
  public successMessage: string = '';

  // Modals
  public showUpgrade: boolean = false;
  public showPayment: boolean = false;
  public showReceipt: boolean = false;
  public showConfirm: boolean = false;

  // Plan selection
  public selectedTier: number | null = null;
  public confirmTierId: number | null = null;

  // Payment form
  public cardName: string = '';
  public cardNumber: string = '';
  public cardExpiry: string = '';
  public cardCvv: string = '';
  public paymentError: string = '';
  public processingPayment: boolean = false;
  public nextBillingDate: string = '';

  constructor(
    private authService: Auth,
    private router: Router,
    private http: HttpClient
  ) {
    addIcons({
      personCircleOutline, mailOutline, shieldCheckmarkOutline,
      logOutOutline, cameraOutline, arrowUpCircleOutline,
      arrowDownCircleOutline, closeCircleOutline,
      checkmarkCircle, closeCircle, cardOutline, lockClosedOutline
    });
  }

  async ngOnInit() {
    this.user = this.authService.getUser();
    if (this.user) await this.loadMembership();
  }

  // ─── LOAD MEMBERSHIP ──────────────────────────────────────────────────────
  async loadMembership() {
    try {
      const headers = new HttpHeaders({ 'Authorization': 'Bearer ' + this.authService.getToken() });
      const res: any = await firstValueFrom(
        this.http.get(`${this.apiUrl}/memberships/${this.user.id}/membership`, { headers })
      );
      this.membership = res.membership;
    } catch (e) {
      this.errorMessage = 'Could not load membership info.';
    }
  }

  // ─── UPGRADE FLOW ─────────────────────────────────────────────────────────
  showUpgradeOptions() {
    this.showUpgrade = true;
    this.selectedTier = null;
    this.errorMessage = '';
    this.successMessage = '';
  }

  selectTier(tierId: number) {
    this.selectedTier = tierId;
  }

  // Called from upgrade picker — go straight to payment
  startUpgrade(tierId: number) {
    this.selectedTier = tierId;
    this.showUpgrade = false;
    this.openPayment();
  }

  proceedToPayment() {
    if (!this.selectedTier) return;
    this.showUpgrade = false;
    this.openPayment();
  }

  openPayment() {
    this.cardName = '';
    this.cardNumber = '';
    this.cardExpiry = '';
    this.cardCvv = '';
    this.paymentError = '';
    this.processingPayment = false;
    this.showPayment = true;
  }

  // ─── CARD FORMATTING ──────────────────────────────────────────────────────
  get cardNumberDisplay(): string {
    const raw = this.cardNumber.replace(/\s/g, '');
    const padded = raw.padEnd(16, '•');
    return padded.match(/.{1,4}/g)?.join(' ') || '•••• •••• •••• ••••';
  }

  formatCardNumber(event: any) {
    let val = event.target.value.replace(/\D/g, '').substring(0, 16);
    this.cardNumber = val.match(/.{1,4}/g)?.join(' ') || val;
  }

  formatExpiry(event: any) {
    let val = event.target.value.replace(/\D/g, '').substring(0, 4);
    if (val.length >= 2) val = val.substring(0, 2) + '/' + val.substring(2);
    this.cardExpiry = val;
  }

  // ─── PAYMENT VALIDATION & PROCESSING ──────────────────────────────────────
  async processPayment() {
    this.paymentError = '';

    // Validate card name
    if (!this.cardName.trim()) {
      this.paymentError = 'Please enter the cardholder name.'; return;
    }

    // Validate card number — must be 16 digits
    const rawNumber = this.cardNumber.replace(/\s/g, '');
    if (rawNumber.length !== 16 || !/^\d+$/.test(rawNumber)) {
      this.paymentError = 'Please enter a valid 16-digit card number.'; return;
    }

    // Validate expiry MM/YY format and not expired
    if (!/^\d{2}\/\d{2}$/.test(this.cardExpiry)) {
      this.paymentError = 'Please enter expiry in MM/YY format.'; return;
    }
    const [month, year] = this.cardExpiry.split('/').map(Number);
    const now = new Date();
    const expDate = new Date(2000 + year, month - 1);
    if (month < 1 || month > 12 || expDate < now) {
      this.paymentError = 'Card has expired or expiry date is invalid.'; return;
    }

    // Validate CVV — must be 3 digits
    if (!/^\d{3}$/.test(this.cardCvv)) {
      this.paymentError = 'Please enter a valid 3-digit CVV.'; return;
    }

    // Simulate processing delay
    this.processingPayment = true;
    await new Promise(resolve => setTimeout(resolve, 2000));

    try {
      // Call the real API to upgrade membership
      await this.changePlan(this.selectedTier!);

      // Calculate next billing date
      const next = new Date();
      next.setMonth(next.getMonth() + 1);
      this.nextBillingDate = next.toLocaleDateString('en-GB', { day: 'numeric', month: 'long', year: 'numeric' });

      this.showPayment = false;
      this.showReceipt = true;

    } catch (e: any) {
      this.paymentError = e?.message || 'Payment failed. Please try again.';
    } finally {
      this.processingPayment = false;
    }
  }

  async closeReceipt() {
    this.showReceipt = false;
    this.successMessage = `Successfully upgraded to ${this.selectedTier === 2 ? 'Premium' : 'Journalism'}!`;
    await this.loadMembership();
  }

  // ─── CONFIRM CHANGE (downgrade/cancel — no payment needed) ───────────────
  confirmChange(tierId: number) {
    this.confirmTierId = tierId;
    this.showConfirm = true;
    this.errorMessage = '';
    this.successMessage = '';
  }

  async applyChange() {
    if (!this.confirmTierId) return;
    await this.changePlan(this.confirmTierId);
    this.showConfirm = false;
  }

  // ─── SHARED PLAN CHANGE ───────────────────────────────────────────────────
  async changePlan(tierId: number) {
    const headers = new HttpHeaders({
      'Authorization': 'Bearer ' + this.authService.getToken(),
      'Content-Type': 'application/json'
    });
    const res: any = await firstValueFrom(
      this.http.patch(
        `${this.apiUrl}/memberships/${this.user.id}/membership`,
        { tier_id: tierId },
        { headers }
      )
    );
    if (!res) throw new Error('Upgrade failed');
    this.successMessage = res.message;
  }

  // ─── PHOTO UPLOAD ─────────────────────────────────────────────────────────
  pickPhoto() { this.photoInput.nativeElement.click(); }

  async onPhotoSelected(event: any) {
    const file = event.target.files[0];
    if (!file) return;
    this.errorMessage = '';
    this.successMessage = '';

    const formData = new FormData();
    formData.append('photo', file);

    try {
      const headers = new HttpHeaders({ 'Authorization': 'Bearer ' + this.authService.getToken() });
      const res: any = await firstValueFrom(
        this.http.post(`${this.apiUrl}/users/${this.user.id}/photo`, formData, { headers })
      );
      this.user.profile_photo = res.photo_url;
      localStorage.setItem('user', JSON.stringify({ ...this.authService.getUser(), profile_photo: res.photo_url }));
      this.successMessage = 'Profile photo updated!';
    } catch (e) {
      this.errorMessage = 'Photo upload failed. Please try again.';
    }
  }

  // ─── LOGOUT ───────────────────────────────────────────────────────────────
  logout() {
    this.authService.logout();
    this.router.navigate(['/login']);
  }
}