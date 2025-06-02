import { Injectable } from '@angular/core';
import { HttpClient, HttpParams, HttpErrorResponse } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { catchError } from 'rxjs/operators';
import { User, Role, ApiResponse } from '../models/user.model';
import { AuthService } from './auth.service';
import { environment } from '../../environments/environment';

@Injectable({
  providedIn: 'root'
})
export class UserService {
  private apiUrl = environment.apiUrl || 'http://localhost:8080/api';

  constructor(
    private http: HttpClient,
    private authService: AuthService
  ) {}

  // User Management
  getAllUsers(page: number = 1, limit: number = 10, search?: string): Observable<ApiResponse<User[]>> {
    let params = new HttpParams()
      .set('page', page.toString())
      .set('limit', limit.toString());
    
    if (search) {
      params = params.set('search', search);
    }

    return this.http.get<ApiResponse<User[]>>(`${this.apiUrl}/users`, {
      headers: this.authService.getAuthHeaders(),
      params
    }).pipe(catchError(this.handleError));
  }

  getUserById(id: number): Observable<ApiResponse<User>> {
    return this.http.get<ApiResponse<User>>(`${this.apiUrl}/users/${id}`, {
      headers: this.authService.getAuthHeaders()
    }).pipe(catchError(this.handleError));
  }

  createUser(user: Partial<User>): Observable<ApiResponse<User>> {
    return this.http.post<ApiResponse<User>>(`${this.apiUrl}/users`, user, {
      headers: this.authService.getAuthHeaders()
    }).pipe(catchError(this.handleError));
  }

  updateUser(id: number, user: Partial<User>): Observable<ApiResponse<User>> {
    return this.http.put<ApiResponse<User>>(`${this.apiUrl}/users/${id}`, user, {
      headers: this.authService.getAuthHeaders()
    }).pipe(catchError(this.handleError));
  }

  deleteUser(id: number): Observable<ApiResponse<any>> {
    return this.http.delete<ApiResponse<any>>(`${this.apiUrl}/users/${id}`, {
      headers: this.authService.getAuthHeaders()
    }).pipe(catchError(this.handleError));
  }

  // Role Management
  getAllRoles(): Observable<ApiResponse<Role[]>> {
    return this.http.get<ApiResponse<Role[]>>(`${this.apiUrl}/roles`, {
      headers: this.authService.getAuthHeaders()
    }).pipe(catchError(this.handleError));
  }

  getRoleById(id: number): Observable<ApiResponse<Role>> {
    return this.http.get<ApiResponse<Role>>(`${this.apiUrl}/roles/${id}`, {
      headers: this.authService.getAuthHeaders()
    }).pipe(catchError(this.handleError));
  }

  createRole(role: Partial<Role>): Observable<ApiResponse<Role>> {
    return this.http.post<ApiResponse<Role>>(`${this.apiUrl}/roles`, role, {
      headers: this.authService.getAuthHeaders()
    }).pipe(catchError(this.handleError));
  }

  updateRole(id: number, role: Partial<Role>): Observable<ApiResponse<Role>> {
    return this.http.put<ApiResponse<Role>>(`${this.apiUrl}/roles/${id}`, role, {
      headers: this.authService.getAuthHeaders()
    }).pipe(catchError(this.handleError));
  }

  deleteRole(id: number): Observable<ApiResponse<any>> {
    return this.http.delete<ApiResponse<any>>(`${this.apiUrl}/roles/${id}`, {
      headers: this.authService.getAuthHeaders()
    }).pipe(catchError(this.handleError));
  }

  // Assign role to user
  assignRoleToUser(userId: number, roleId: number): Observable<ApiResponse<any>> {
    return this.http.post<ApiResponse<any>>(`${this.apiUrl}/users/${userId}/assign-role`, 
      { role_id: roleId }, 
      { headers: this.authService.getAuthHeaders() }
    ).pipe(catchError(this.handleError));
  }

  private handleError(error: HttpErrorResponse): Observable<never> {
    let errorMessage = 'An error occurred';
    
    if (error.error instanceof ErrorEvent) {
      errorMessage = `Error: ${error.error.message}`;
    } else {
      errorMessage = `Error Code: ${error.status}\nMessage: ${error.message}`;
    }
    
    console.error('UserService Error:', errorMessage);
    return throwError(() => new Error(errorMessage));
  }
}
