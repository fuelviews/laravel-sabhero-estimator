<?php

namespace Fuelviews\SabHeroEstimator\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FormSubmissionService
{
    protected string $endpoint;

    public function __construct()
    {
        $this->endpoint = $this->getEndpoint();
    }

    /**
     * Submit project data to external API
     */
    public function submit(array $projectData): bool
    {
        if (! config('sabhero-estimator.form_endpoints.enabled') || ! $this->endpoint) {
            return false;
        }

        try {
            $payload = $this->preparePayload($projectData);

            $response = Http::withHeaders([
                'Content-Type' => 'application/json',
                'User-Agent' => 'Mozilla/5.0 (compatible; SabHeroEstimator/1.0)',
            ])
                ->withBody($payload, 'application/json')
                ->post($this->endpoint);

            if ($response->successful()) {
                Log::info('Form submission successful', [
                    'response' => $response->json() ?? [],
                    'status' => $response->status(),
                ]);

                return true;
            }

            Log::error('Form submission failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'endpoint' => $this->endpoint,
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('Exception during form submission', [
                'message' => $e->getMessage(),
                'endpoint' => $this->endpoint,
            ]);

            return false;
        }
    }

    /**
     * Get the appropriate endpoint based on environment
     */
    protected function getEndpoint(): string
    {
        if (app()->environment('production')) {
            return config('sabhero-estimator.form_endpoints.production_url', '');
        }

        return config('sabhero-estimator.form_endpoints.development_url', '');
    }

    /**
     * Prepare payload for submission
     */
    protected function preparePayload(array $projectData): string
    {
        // Flatten areas array for API compatibility
        $flatAreasString = '';
        if (! empty($projectData['areas']) && is_array($projectData['areas'])) {
            $flatAreas = array_map(function ($area) {
                $areaName = $area['name'] ?? '';
                $flatSurfaces = array_map(function ($surface) {
                    return $surface['surface_type'].'|'.$surface['measurement'].'|'.$surface['quantity'];
                }, $area['surfaces'] ?? []);

                return $areaName.':'.implode(',', $flatSurfaces);
            }, $projectData['areas']);

            $flatAreasString = implode(';', $flatAreas);
        }

        // Build payload without complex arrays
        $payload = [
            'project_type' => $projectData['project_type'],
            'name' => $projectData['name'],
            'email' => $projectData['email'],
            'phone' => $projectData['phone'],
            'address' => $projectData['address'],
            'estimated_low' => $projectData['estimated_low'],
            'estimated_high' => $projectData['estimated_high'],
            'areas' => $flatAreasString,
        ];

        // Add exterior details if present
        if (! empty($projectData['exterior_details'])) {
            $payload = array_merge($payload, $projectData['exterior_details']);
        }

        return json_encode($payload);
    }

    /**
     * Test the API endpoint connection
     */
    public function testConnection(): array
    {
        if (! $this->endpoint) {
            return [
                'success' => false,
                'message' => 'No endpoint configured',
            ];
        }

        try {
            $response = Http::timeout(10)->get($this->endpoint);

            return [
                'success' => $response->status() < 500,
                'status' => $response->status(),
                'message' => $response->successful() ? 'Connection successful' : 'Connection failed',
                'endpoint' => $this->endpoint,
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => $e->getMessage(),
                'endpoint' => $this->endpoint,
            ];
        }
    }
}
