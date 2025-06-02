import { Component } from '@angular/core';
import { RouterOutlet, Router } from '@angular/router';
import { MatToolbarModule } from '@angular/material/toolbar';
import { MatButtonModule } from '@angular/material/button';
import { MatIconModule } from '@angular/material/icon';
import { MatSidenavModule } from '@angular/material/sidenav';
import { MatListModule } from '@angular/material/list';
import { CommonModule } from '@angular/common';

@Component({
  selector: 'app-layout',
  standalone: true,
  imports: [
    CommonModule,
    RouterOutlet,
    MatToolbarModule,
    MatButtonModule,
    MatIconModule,
    MatSidenavModule,
    MatListModule
  ],
  template: `
    <div class="app-container">
      <mat-sidenav-container class="sidenav-container">
        <mat-sidenav #drawer class="sidenav" fixedInViewport="true" mode="side" opened="true">
          <div class="sidenav-header">
            <h3>EMS Portal</h3>
          </div>
          <mat-nav-list>
            <a mat-list-item routerLink="/dashboard" routerLinkActive="active-link">
              <mat-icon matListItemIcon>dashboard</mat-icon>
              <span matListItemTitle>Dashboard</span>
            </a>
            <a mat-list-item routerLink="/employees" routerLinkActive="active-link">
              <mat-icon matListItemIcon>people</mat-icon>
              <span matListItemTitle>Employees</span>
            </a>
            <a mat-list-item routerLink="/employees/add" routerLinkActive="active-link">
              <mat-icon matListItemIcon>person_add</mat-icon>
              <span matListItemTitle>Add Employee</span>
            </a>
            <a mat-list-item routerLink="/reports" routerLinkActive="active-link">
              <mat-icon matListItemIcon>assessment</mat-icon>
              <span matListItemTitle>Reports</span>
            </a>
          </mat-nav-list>
        </mat-sidenav>

        <mat-sidenav-content>
          <mat-toolbar class="toolbar">
            <button
              type="button"
              aria-label="Toggle sidenav"
              mat-icon-button
              (click)="drawer.toggle()">
              <mat-icon aria-label="Side nav toggle icon">menu</mat-icon>
            </button>
            <span class="toolbar-title">Employee Management System</span>
            <span class="spacer"></span>
            <button mat-icon-button>
              <mat-icon>account_circle</mat-icon>
            </button>
          </mat-toolbar>

          <div class="content">
            <router-outlet></router-outlet>
          </div>
        </mat-sidenav-content>
      </mat-sidenav-container>
    </div>
  `,
  styles: [`
    .app-container {
      height: 100vh;
      display: flex;
      flex-direction: column;
    }

    .sidenav-container {
      flex: 1;
    }

    .sidenav {
      width: 250px;
      background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
      border-right: 1px solid #dee2e6;
    }

    .sidenav-header {
      padding: 20px 16px;
      border-bottom: 1px solid #dee2e6;
      background: linear-gradient(135deg, #495057 0%, #343a40 100%);
      color: white;
    }

    .sidenav-header h3 {
      margin: 0;
      font-size: 18px;
      font-weight: 600;
    }

    .toolbar {
      background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
      border-bottom: 1px solid #dee2e6;
      color: #495057;
    }

    .toolbar-title {
      font-size: 20px;
      font-weight: 600;
    }

    .spacer {
      flex: 1 1 auto;
    }

    .content {
      padding: 24px;
      background: #f8f9fa;
      min-height: calc(100vh - 64px);
    }

    .active-link {
      background-color: rgba(69, 90, 100, 0.1);
      color: #455a64;
    }

    .mat-mdc-list-item {
      margin-bottom: 4px;
    }

    .mat-mdc-list-item:hover {
      background-color: rgba(69, 90, 100, 0.05);
    }
  `]
})
export class LayoutComponent {
  constructor(private router: Router) {}
}
