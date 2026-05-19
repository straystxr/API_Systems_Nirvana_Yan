import { Component, Input, OnInit } from '@angular/core';
// CommonModule provides basic Angular directives like *ngIf and *ngFor
import { CommonModule } from '@angular/common';
import { ArticleService } from '../../services/article'

//FormBuilder is to create a form for the articles and FormGroup is the container that holds all form controls together
//validators provides built-in validation rules like required and minLength so the article is never too short,
//for example and that there should be a minimum amount of charas
//ReactiveFormsModule is Angular's reactive form system into the template
import { FormBuilder, FormGroup, Validators, ReactiveFormsModule } from '@angular/forms';
import {
  IonHeader, IonToolbar, IonTitle, IonContent,
  IonButton, IonButtons, IonItem, IonLabel,
  IonInput, IonTextarea, IonSelect, IonSelectOption,
  ModalController
} from '@ionic/angular/standalone';

//adding all the components as its a standalone
@Component({
  selector: 'app-article-modal',
  standalone: true,
  imports: [
    CommonModule,
    ReactiveFormsModule,
    IonHeader, IonToolbar, IonTitle, IonContent,
    IonButton, IonButtons, IonItem, IonLabel,
    IonInput, IonTextarea, IonSelect, IonSelectOption,
  ],
  //this is how a new article creation should be structed every time using the template keyword
  //using html to actually showcase the form
  template: `
    <ion-header>
      <ion-toolbar>
        <ion-buttons slot="start">
          <!-- Cancel dismisses the modal and passes null back with role 'cancel' -->
          <ion-button (click)="dismiss()" color="medium">Cancel</ion-button>
        </ion-buttons>
        <ion-title>New Article</ion-title>
        <ion-buttons slot="end">
          <!--publish button is disabled until all form fields pass validation -->
          <!--strong="true" makes the text bold to signal it is the primary action -->
          <ion-button
            (click)="submit()"
            [disabled]="form.invalid"
            color="primary"
            strong="true">
            Publish
          </ion-button>
        </ion-buttons>
      </ion-toolbar>
    </ion-header>

    <ion-content class="ion-padding">
      <!--[formGroup] binds this form element creating a reactive FormGroup instance -->
      <form [formGroup]="form">

        <!--Author name field — no auth system yet so user types their name -->
        <ion-item>
          <ion-label position="stacked">Your Name</ion-label>
          <!-- formControlName links this input to the 'authorName' control in the FormGroup -->
          <ion-input
            formControlName="authorName"
            placeholder="Enter your name">
          </ion-input>
        </ion-item>

        <ion-item style="margin-top: 12px;">
          <ion-label position="stacked">Title</ion-label>
          <!--counter="true" shows a live character count below the input -->
          <!--maxlength="100" limits input and feeds the counter display -->
          <ion-input
            formControlName="title"
            placeholder="Enter article title"
            [counter]="true"
            maxlength="100">
          </ion-input>
        </ion-item>

        <ion-item style="margin-top: 12px;">
          <ion-label position="stacked">Category</ion-label>
          <!-- ion-select renders as a native picker on iOS/Android -->
          <ion-select formControlName="category" placeholder="Select a category">
            <ion-select-option value="news">News</ion-select-option>
            <ion-select-option value="sport">Sport</ion-select-option>
            <ion-select-option value="culture">Culture</ion-select-option>
            <ion-select-option value="politics">Politics</ion-select-option>
            <ion-select-option value="other">Other</ion-select-option>
          </ion-select>
        </ion-item>

        <ion-item style="margin-top: 12px;">
          <ion-label position="stacked">Body</ion-label>
          <!-- autoGrow="true" makes the textarea expand as the user types -->
          <!-- rows="8" sets the initial height before autoGrow kicks in -->
          <ion-textarea
            formControlName="body"
            placeholder="Write your article here..."
            [autoGrow]="true"
            [counter]="true"
            maxlength="2000"
            rows="8">
          </ion-textarea>
        </ion-item>

        <!--shows the exact coordinates where the user tapped the map rounds to 5 decimal places -->
        <p style="font-size: 12px; color: var(--ion-color-medium); padding: 12px 16px 0;">
          Pinned at: {{ lat.toFixed(5) }}, {{ lng.toFixed(5) }}
        </p>

      </form>
    </ion-content>
  `
})
export class ArticleModalComponent implements OnInit {

  //@Input() means these values are passed in from the parent component
  //tab1.page.ts passes the lat/lng of where the user tapped
  @Input() lat!: number;
  @Input() lng!: number;

  form!: FormGroup;

  constructor(
    //formBuilder is a helper service that makes creating form controls cleaner
    private fb: FormBuilder,
    //ModalController lets this component close itself and pass data back
    private modalCtrl: ModalController,
    private articleService: ArticleService
  ) {}

  //ngOnInit runs after Angular has set all @Input() values
  //Builds the form
  ngOnInit() {
    this.form = this.fb.group({
      // Each key matches the formControlName used in the template
      // The array format is [initialValue, validators]
      authorName: ['', Validators.required],
      title:      ['', [Validators.required, Validators.minLength(5)]],
      category:   ['', Validators.required],
      // minLength(20) encourages a real article body rather than one word
      body:       ['', [Validators.required, Validators.minLength(20)]],
    });
  }

  //called when the user taps Cancel
  //passes null as data and 'cancel' as the role
  dismiss() {
    this.modalCtrl.dismiss(null, 'cancel');
  }

  // Called when the user taps Publish
  // The parent reads role === 'confirm' to know it should save the article
submit() {
  if (this.form.invalid) return;

  console.log('Form value being sent:', this.form.value);

  const token = localStorage.getItem('access_token') ?? '';
this.articleService.submitArticle(this.form.value, token).subscribe({
    next: (res: any) => {
  console.log('Full response:', JSON.stringify(res)); 
  if (res && res.success) {
    this.modalCtrl.dismiss(this.form.value, 'confirm');
  } else {
    console.error('Failed to save:', res?.message);
  }
},
    error: (err: any) => {
      console.error('HTTP error:', err);
    }
  });
  
}
}