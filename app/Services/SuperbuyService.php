<?php

namespace App\Services;

use App\Models\Parcel;
use App\Models\Item;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Symfony\Component\DomCrawler\Crawler;

class SuperbuyService
{
    public function syncOrdersToInventory(int $userId): int
    {
        $crawler = $this->fetchOrderCrawler();

        $items = $crawler->filter('.order-list-item, .order-item, .order-list .item');
        if ($items->count() === 0) {
            $items = $crawler->filter('table tr');
        }

        $imported = 0;

        $items->each(function (Crawler $node) use (&$imported, $userId) {
            $nodeText = trim($node->text(''));

            $orderNo = $this->extractText($node, '.order-no')
                ?: $this->extractText($node, '.order-number')
                ?: $this->extractText($node, '.orderId')
                ?: $this->extractByRegex($nodeText, '/Order\s*No[:：]?\s*([A-Z0-9\-]+)/i')
                ?: $this->extractByRegex($nodeText, '/\bDO\d{6,}\b/i');

            $description = $this->extractText($node, '.item-name')
                ?: $this->extractText($node, '.goods-name')
                ?: $this->extractText($node, '.order-title')
                ?: $this->extractText($node, '.item-title')
                ?: $this->extractByRegex($nodeText, '/Item\s*Name[:：]?\s*([^\n]+)/i');

            $status = $this->extractText($node, '.status')
                ?: $this->extractText($node, '.order-status')
                ?: $this->extractText($node, '.status-text')
                ?: $this->extractByRegex($nodeText, '/Status[:：]?\s*([^\n]+)/i');

            $price = $this->extractByRegex($nodeText, '/(?:€|\$|US|EU|CNY)\s*([0-9]+[\.,]?[0-9]*)/i');
            $buyPrice = $price ? floatval(str_replace(',', '.', $price)) : 0;

            $orderNo = $orderNo ? trim($orderNo) : null;
            $description = $description ? trim($description) : null;
            $status = $status ? trim($status) : 'prep';

            if (!$orderNo) {
                return;
            }

            $item = Item::firstOrCreate(
                [
                    'user_id' => $userId,
                    'item_no' => $orderNo,
                    'name' => $description ?: "Order $orderNo",
                ],
                [
                    'order_nmr' => $orderNo,
                    'buy_price' => $buyPrice,
                    'status' => $this->normalizeInventoryStatus($status),
                    'is_sold' => false,
                    'source_link' => 'https://www.superbuy.com/order',
                ]
            );

            if ($item->wasRecentlyCreated) {
                $imported++;
            } elseif (empty($item->order_nmr)) {
                $item->order_nmr = $orderNo;
                $item->save();
            }
        });

        return $imported;
    }

    public function syncParcels(int $userId): int
    {
        $crawler = $this->fetchOrderCrawler();

        $items = $crawler->filter('.order-list-item, .order-item, .order-list .item');
        if ($items->count() === 0) {
            $items = $crawler->filter('table tr');
        }

        $imported = 0;

        $items->each(function (Crawler $node) use (&$imported, $userId) {
            $nodeText = trim($node->text(''));

            $orderNo = $this->extractText($node, '.order-no')
                ?: $this->extractText($node, '.order-number')
                ?: $this->extractText($node, '.orderId')
                ?: $this->extractByRegex($nodeText, '/Order\s*No[:：]?\s*([A-Z0-9\-]+)/i')
                ?: $this->extractByRegex($nodeText, '/\bDO\d{6,}\b/i');

            $trackingNumber = $this->extractText($node, '.tracking-number')
                ?: $this->extractText($node, '.tracking')
                ?: $this->extractByRegex($nodeText, '/Tracking\s*(?:No)?[:：]?\s*([A-Z0-9\-]+)/i');

            $status = $this->extractText($node, '.status')
                ?: $this->extractText($node, '.order-status')
                ?: $this->extractText($node, '.status-text')
                ?: $this->extractByRegex($nodeText, '/Status[:：]?\s*([^\n]+)/i');

            $description = $this->extractText($node, '.item-name')
                ?: $this->extractText($node, '.goods-name')
                ?: $this->extractText($node, '.order-title')
                ?: $this->extractText($node, '.item-title')
                ?: $this->extractByRegex($nodeText, '/Item\s*Name[:：]?\s*([^\n]+)/i');

            $orderNo = $orderNo ? trim($orderNo) : null;
            $trackingNumber = $trackingNumber ? trim($trackingNumber) : null;
            $status = $status ? trim($status) : 'prep';
            $description = $description ? trim($description) : null;

            if (!$orderNo) {
                return;
            }

            $parcel = Parcel::firstOrCreate(
                [
                    'user_id' => $userId,
                    'parcel_no' => $orderNo,
                ],
                [
                    'tracking_code' => $trackingNumber,
                    'status' => $this->normalizeStatus($status),
                    'description' => $description,
                ]
            );

            if (!$parcel->wasRecentlyCreated) {
                $updates = [];
                if ($trackingNumber && !$parcel->tracking_code) {
                    $updates['tracking_code'] = $trackingNumber;
                }
                if ($description && !$parcel->description) {
                    $updates['description'] = $description;
                }
                if ($updates) {
                    $parcel->update($updates);
                }
            } else {
                $imported++;
            }
        });

        return $imported;
    }

    private function fetchOrderCrawler(): Crawler
    {
        $cookie = config('services.superbuy.cookie');
        $userAgent = config('services.superbuy.user_agent');

        if (!$cookie || !$userAgent) {
            throw new \RuntimeException('Superbuy credentials ontbreken in config.');
        }

        $response = Http::withHeaders([
            'Cookie' => $cookie,
            'User-Agent' => $userAgent,
            'Accept' => 'text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
        ])->get('https://www.superbuy.com/order');

        if (!$response->successful()) {
            throw new \RuntimeException('Superbuy request failed: ' . $response->status());
        }

        return new Crawler($response->body());
    }

    private function extractText(Crawler $node, string $selector): ?string
    {
        try {
            $text = $node->filter($selector)->first()->text('');
            return trim($text) !== '' ? $text : null;
        } catch (\InvalidArgumentException $e) {
            return null;
        }
    }

    private function extractFromRow(Crawler $node, int $index): ?string
    {
        try {
            $cells = $node->filter('td');
            if ($cells->count() > $index) {
                return trim($cells->eq($index)->text('')) ?: null;
            }
        } catch (\InvalidArgumentException $e) {
            return null;
        }

        return null;
    }

    private function extractByRegex(string $text, string $pattern): ?string
    {
        if (preg_match($pattern, $text, $matches)) {
            return $matches[1] ?? $matches[0] ?? null;
        }

        return null;
    }

    private function normalizeStatus(string $status): string
    {
        $statusLower = Str::lower($status);

        return match (true) {
            str_contains($statusLower, 'arriv') => 'arrived',
            str_contains($statusLower, 'ship') => 'shipped',
            str_contains($statusLower, 'transit') => 'shipped',
            default => 'prep',
        };
    }

    private function normalizeInventoryStatus(string $status): string
    {
        $statusLower = Str::lower($status);

        return match (true) {
            str_contains($statusLower, 'received') => 'online',
            str_contains($statusLower, 'arriv') => 'online',
            str_contains($statusLower, 'ship') => 'prep',
            str_contains($statusLower, 'paid') => 'todo',
            default => 'todo',
        };
    }
}
