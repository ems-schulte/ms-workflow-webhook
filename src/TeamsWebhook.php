<?php

namespace EmsSchulte\MsWorkflowWebhook;

class TeamsWebhook
{
	const CONTENT_APPLICATION_TYPE = "application/vnd.microsoft.card.adaptive";
	const CONTENT_URL = null;
    const CONTENT_SCHEMA_URL = "http://adaptivecards.io/schemas/adaptive-card.json";
    const CONTENT_TYPE = "AdaptiveCard";
    const CONTENT_VERSION = "1.2";


    private $webhookUrl;

    public function __construct($webhookUrl)
    {
        $this->webhookUrl = $webhookUrl;
    }

	public function sendCard(string $description, array $properties = null): string
	{
		$cardContent = $this->contentBody($description, $properties);

		return $this->sendWebhook($cardContent);
	}

    private function contentBody(string $description, array $properties = null, string $title = null, string $creatorName = null, string $creatorProfileImage = null, string $createdUtc = null, string $viewUrl = null): array
    {
        if (empty($description)) {
            trigger_error("Description is required", E_USER_ERROR);
        }

        $body = [];

        if (!empty($title)) {
            $body[] = $this->createTextBlock($title);
        }

        if (!empty($creatorName)) {
            $creatorColumn = $this->createColumn([
                $this->createTextBlock($creatorName, "medium", "bolder")
            ], "stretch");

            if (!empty($createdUtc)) {
                $creatorColumn['items'][] = $this->createTextBlock("Created {{DATE($createdUtc, SHORT)}}");
            }

            $columns = [
                $this->createColumn([
                    $this->createImage($creatorProfileImage, $creatorName)
                ]),
                $creatorColumn
            ];

            $body[] = $this->createColumnSet($columns);
        }

        $body[] = $this->createTextBlock($description);

        if (!empty($properties)) {
            $body[] = $this->createFactSet($properties);
        }

        return $body;
    }

    private function createTextBlock(string $text, string $size = "medium", string $weight = "bolder", string $style = "heading"): array
    {
        return [
            "type" => "TextBlock",
            /*"size" => $size,
            "weight" => $weight,*/
            "text" => $text/*,
            "style" => $style,
            "wrap" => true*/
        ];
    }

    private function createColumnSet(array $columns): array
    {
        return [
            "type" => "ColumnSet",
            "columns" => $columns
        ];
    }

    private function createColumn(array $items, string $width = "auto"): array
    {
        return [
            "type" => "Column",
            "items" => $items,
            "width" => $width
        ];
    }

    private function createImage(string $url, string $altText, string $size = "small", string $style = "person"): array
    {
        return [
            "type" => "Image",
            "style" => $style,
            "url" => $url,
            "altText" => $altText,
            "size" => $size
        ];
    }

    private function createFactSet(array $properties): array
    {
        return [
            "type" => "FactSet",
            "facts" => array_map(function($property) {
                return [
                    "title" => $property['key'] . ":",
                    "value" => $property['value']
                ];
            }, $properties)
        ];
    }

    private function sendWebhook(array $cardContent): string
    {
        $data = json_encode([
            "type" => "message",
            "attachments" => [
                [
                    "contentType" => self::CONTENT_APPLICATION_TYPE,
                    "contentUrl" => self::CONTENT_URL,
                    "content" => [
                        '$schema' => self::CONTENT_SCHEMA_URL,
                        "type" => self::CONTENT_TYPE,
                        "version" => self::CONTENT_VERSION,
                        "body" => $cardContent
                    ]
                ]
            ]
        ]);

        $ch = curl_init($this->webhookUrl);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $response = curl_exec($ch);
        curl_close($ch);

        return $response;
    }
}
