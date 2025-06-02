<?php

namespace App\Controllers;

use CodeIgniter\RESTful\ResourceController;
use CodeIgniter\API\ResponseTrait;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class ApiController extends ResourceController
{
    use ResponseTrait;

    protected $modelName;
    protected $format = 'json';

    public function __construct()
    {
        // Enable CORS
        $this->enableCORS();
    }

    protected function enableCORS()
    {
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit();
        }
    }

    /**
     * Validate JWT token
     */
    protected function validateToken()
    {
        $key = getenv('JWT_SECRET') ?: 'your-secret-key';
        $header = $this->request->getHeaderLine('Authorization');
        
        if (empty($header)) {
            return $this->failUnauthorized('Authorization header missing');
        }

        try {
            $token = str_replace('Bearer ', '', $header);
            $decoded = JWT::decode($token, new Key($key, 'HS256'));
            return $decoded;
        } catch (\Exception $e) {
            return $this->failUnauthorized('Invalid token: ' . $e->getMessage());
        }
    }

    /**
     * Check if user has required permission
     */
    protected function checkPermission($permission)
    {
        $user = $this->validateToken();
        if (is_object($user) && isset($user->permissions)) {
            if (in_array($permission, $user->permissions)) {
                return $user;
            }
        }
        return $this->failForbidden('Insufficient permissions');
    }

    /**
     * Get pagination parameters
     */
    protected function getPaginationParams()
    {
        $page = (int) $this->request->getGet('page') ?: 1;
        $limit = (int) $this->request->getGet('limit') ?: 10;
        $offset = ($page - 1) * $limit;

        return [
            'page' => $page,
            'limit' => $limit,
            'offset' => $offset
        ];
    }

    /**
     * Format paginated response
     */
    protected function paginatedResponse($data, $total, $page, $limit)
    {
        $totalPages = ceil($total / $limit);
        
        return $this->respond([
            'status' => true,
            'data' => $data,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_items' => $total,
                'items_per_page' => $limit,
                'has_next' => $page < $totalPages,
                'has_prev' => $page > 1
            ]
        ]);
    }

    /**
     * Standard success response
     */
    protected function successResponse($data = null, $message = 'Success', $code = 200)
    {
        return $this->respond([
            'status' => true,
            'message' => $message,
            'data' => $data
        ], $code);
    }

    /**
     * Standard error response
     */
    protected function errorResponse($message = 'Error', $errors = null, $code = 400)
    {
        $response = [
            'status' => false,
            'message' => $message
        ];

        if ($errors !== null) {
            $response['errors'] = $errors;
        }

        return $this->respond($response, $code);
    }

    /**
     * Validation error response
     */
    protected function validationErrorResponse($errors)
    {
        return $this->errorResponse('Validation failed', $errors, 422);
    }

    /**
     * Handle model validation errors
     */
    protected function handleValidationErrors($model)
    {
        $errors = $model->errors();
        return $this->validationErrorResponse($errors);
    }
}
