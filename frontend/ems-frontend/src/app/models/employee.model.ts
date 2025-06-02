export interface Employee {
  id?: number;
  employee_id: string;
  first_name: string;
  last_name: string;
  email: string;
  phone?: string;
  department: string;
  position: string;
  salary?: number;
  hire_date: string;
  status: 'active' | 'inactive';
  address?: string;
  date_of_birth?: string;
  emergency_contact?: string;
  emergency_phone?: string;
  created_at?: string;
  updated_at?: string;
}

export interface CreateEmployeeRequest {
  employee_id: string;
  first_name: string;
  last_name: string;
  email: string;
  phone?: string;
  department: string;
  position: string;
  salary?: number;
  hire_date: string;
  address?: string;
  date_of_birth?: string;
  emergency_contact?: string;
  emergency_phone?: string;
}

export interface EmployeeResponse {
  success: boolean;
  message: string;
  data?: Employee[];
  employee?: Employee;
  total?: number;
}

export interface UpdateEmployeeRequest extends CreateEmployeeRequest {
  id: number;
  status: 'active' | 'inactive';
}

export interface EmployeeResponse {
  success: boolean;
  message: string;
  data?: Employee | Employee[];
}
