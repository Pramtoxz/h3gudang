<?php

namespace App\Helpers;

use App\Models\PartCategoryImage;
use App\Models\Product;

class PartHelper
{
    public const DEFAULT_IMAGE = 'https://pmo.menara-agung.com/assets/images/lg_honda.jpg';

    public static function getPartImage(?string $kodePart, ?Product $product = null, mixed $part = null): string
    {
        if ($product?->gambar) {
            return url('images/part/' . $product->gambar);
        }

        if (! $product && $kodePart) {
            $product = Product::where('kode_part', $kodePart)->first();

            if ($product?->gambar) {
                return url('images/part/' . $product->gambar);
            }
        }

        if ($part?->fk_detail_sub_kelompok_part) {
            $gambarKategori = PartCategoryImage::where('kode_kelompok', $part->fk_detail_sub_kelompok_part)->first();

            if ($gambarKategori?->gambar) {
                return url('images/category/' . $gambarKategori->gambar);
            }
        }

        return self::DEFAULT_IMAGE;
    }

    public static function getPartName(mixed $part, ?Product $product = null): string
    {
        if ($product?->nama) {
            return $product->nama;
        }

        return $part?->nm_part ?? '-';
    }

    public static function getPartDescription(mixed $part, ?Product $product = null): string
    {
        if ($product?->deskripsi) {
            return $product->deskripsi;
        }

        return self::getPartName($part, $product);
    }
}
