<?php
namespace DevNoKage;

use DevNoKage\Abstract\AbstractEntity;

class Response extends AbstractEntity
{

    protected $data;
    protected string $message;
    protected string $statut;
    protected int $code;

    public function __construct(
        string $message = '',
        string $statut = 'error',
        int $code = 404,
        $data = null
    ) {
        $this->data = $data;
        $this->statut = $statut;
        $this->code = $code;
        $this->message = $message;
    }

    public function toArray(): array
    {
        return [
            'data' => $this->data,
            'statut' => $this->statut,
            'code' => $this->code,
            'message' => $this->message,
        ];
    }

    public static function toObject(array $data): static
    {
        return new static(
            $data['message'] ?? '',
            $data['statut'] ?? 'error',
            $data['code'] ?? 404,
            $data['data'] ?? null
        );
    }

    /**
     * Méthode statique pour envoyer une réponse JSON
     */
    public static function json(array $data, int $httpCode = 200): void
    {
        http_response_code($httpCode);
        header('Content-Type: application/json; charset=utf-8');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With');
        
        // Gérer les requêtes OPTIONS (preflight)
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            exit(0);
        }
        
        echo json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        exit;
    }

    /**
     * Créer une réponse de succès
     */
    public static function success($data, string $message = 'Opération réussie', int $code = 200): array
    {
        return [
            'data' => $data,
            'statut' => 'success',
            'code' => $code,
            'message' => $message
        ];
    }

    /**
     * Créer une réponse d'erreur
     */
    public static function error(string $message = 'Une erreur est survenue', int $code = 400, $data = null): array
    {
        return [
            'data' => $data,
            'statut' => 'error',
            'code' => $code,
            'message' => $message
        ];
    }

    // Getters et Setters
    public function getData()
    {
        return $this->data;
    }

    public function setData($data): void
    {
        $this->data = $data;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function setMessage(string $message): void
    {
        $this->message = $message;
    }

    public function getStatut(): string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): void
    {
        $this->statut = $statut;
    }

    public function getCode(): int
    {
        return $this->code;
    }

    public function setCode(int $code): void
    {
        $this->code = $code;
    }
}
