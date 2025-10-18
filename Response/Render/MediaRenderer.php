<?php
namespace Response\Render;

use Response\HTTPRenderer;

class MediaRenderer implements HTTPRenderer
{
    public function __construct(private string $filepathBase, private string $type) {}

    public function getFields(): array
    {
        return [
            'Content-Type' => $this->getTypeDetails()['content_type'],
            'X-Content-Type-Options' => 'nosniff',
        ];
    }

    public function getFileName(): string
    {
        $base = __DIR__ . '/../..'; // project root (adjust if needed)
        $filename = sprintf("%s/%s.%s", $base, $this->filepathBase, $this->getTypeDetails()['extension']);
        if (file_exists($filename)) {
            return $filename;
        }
        return sprintf("%s/%s", $base, "public/images/file-not-found.jpg");
    }

    public function getContent(): string
    {
        ob_start();
        readfile($this->getFileName());
        $data = ob_get_contents();
        ob_end_clean();
        return $data !== false ? $data : '';
    }

    private function getTypeDetails(): array
    {
        $supported = [
            'jpg'  => ['content_type' => 'image/jpeg', 'extension' => 'jpg'],
            'jpeg' => ['content_type' => 'image/jpeg', 'extension' => 'jpeg'],
            'png'  => ['content_type' => 'image/png',  'extension' => 'png'],
            'gif'  => ['content_type' => 'image/gif',  'extension' => 'gif'],
            'mp3'  => ['content_type' => 'audio/mpeg', 'extension' => 'mp3'],
            'mp4'  => ['content_type' => 'video/mp4',  'extension' => 'mp4'],
        ];
        if (!isset($supported[$this->type])) {
            throw new \InvalidArgumentException(sprintf("Media type %s is an invalid type", $this->type));
        }
        return $supported[$this->type];
    }
}
