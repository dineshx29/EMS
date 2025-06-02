import { Injectable } from '@angular/core';
import { HttpClient, HttpErrorResponse, HttpParams } from '@angular/common/http';
import { Observable, throwError } from 'rxjs';
import { catchError, map } from 'rxjs/operators';
import { Employee, CreateEmployeeRequest, EmployeeResponse } from '../models/employee.model';
import { AuthService } from './auth.service';
import { environment } from '../../environments/environment';

@Injectable({
  providedIn: 'root'
})
export class EmployeeService {
  private apiUrl = environment.apiUrl || 'http://localhost:8080/api';

  constructor(
    private http: HttpClient,
    private authService: AuthService
  ) {}

  getAllEmployees(page: number = 1, limit: number = 10, search?: string): Observable<EmployeeResponse> {
    let params = new HttpParams()
      .set('page', page.toString())
      .set('limit', limit.toString());
    
    if (search) {
      params = params.set('search', search);
    }

    return this.http.get<EmployeeResponse>(`${this.apiUrl}/employees`, {
      headers: this.authService.getAuthHeaders(),
      params
    }).pipe(catchError(this.handleError));
  }

  getEmployeeById(id: number): Observable<EmployeeResponse> {
    return this.http.get<EmployeeResponse>(`${this.apiUrl}/employees/${id}`, {
      headers: this.authService.getAuthHeaders()
    }).pipe(catchError(this.handleError));
  }

  createEmployee(employee: CreateEmployeeRequest): Observable<EmployeeResponse> {
    return this.http.post<EmployeeResponse>(`${this.apiUrl}/employees`, employee, {
      headers: this.authService.getAuthHeaders()
    }).pipe(catchError(this.handleError));
  }

  updateEmployee(id: number, employee: Partial<Employee>): Observable<EmployeeResponse> {
    return this.http.put<EmployeeResponse>(`${this.apiUrl}/employees/${id}`, employee, {
      headers: this.authService.getAuthHeaders()
    }).pipe(catchError(this.handleError));
  }

  deleteEmployee(id: number): Observable<EmployeeResponse> {
    return this.http.delete<EmployeeResponse>(`${this.apiUrl}/employees/${id}`, {
      headers: this.authService.getAuthHeaders()
    }).pipe(catchError(this.handleError));
  }

  getEmployeeStats(): Observable<any> {
    return this.http.get(`${this.apiUrl}/employees/stats`, {
      headers: this.authService.getAuthHeaders()
    }).pipe(catchError(this.handleError));
  }

  /**
   * Handle HTTP errors
   */
  private handleError(error: HttpErrorResponse): Observable<never> {
    let errorMessage = 'An error occurred';
    
    if (error.error instanceof ErrorEvent) {
      errorMessage = `Error: ${error.error.message}`;
    } else {
      errorMessage = `Error Code: ${error.status}\nMessage: ${error.message}`;
    }
    
    console.error('EmployeeService Error:', errorMessage);
    return throwError(() => new Error(errorMessage));
  }
}
