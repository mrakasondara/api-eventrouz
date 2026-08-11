<?php
    namespace App\Services;

    use Illuminate\Http\UploadedFile;
    use Illuminate\Support\Facades\Http;

    class AppwriteStorageService
    {
        protected string $endpoint;
        protected string $projectId;
        protected string $apiKey;
        protected string $bucketId;

        public function __construct()
        {

            $this->endpoint = config('services.appwrite.endpoint');
            $this->projectId = config('services.appwrite.project_id');
            $this->apiKey = config('services.appwrite.api_key');
            $this->bucketId = config('services.appwrite.bucket_id');

        }

        public function uploadFile(UploadedFile $file): array
        {
           $response = Http::withHeaders([
            'x-appwrite-project' => $this->projectId,
            'x-appwrite-key' => $this->apiKey,
           ])
           ->attach(
            'file',
            file_get_contents($file->getRealPath()),
            $file->getClientOriginalName()
           )
           ->post("{$this->endpoint}/storage/buckets/{$this->bucketId}/files", [
            'fileId' => 'unique()',
           ]);

           if($response->failed()){
                throw new \Exception('Appwrite upload error: ' . $response->body());
           }

           return $response->json();
        }

        public function getFileViewUrl(?string $fileId): ?string
        {
            if(!$fileId) return null;

            return "{$this->endpoint}/storage/buckets/{$this->bucketId}/files/{$fileId}/view?project={$this->projectId}";
        }
        
    }
    