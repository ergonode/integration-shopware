<?php
declare(strict_types=1);

namespace Ergonode\IntegrationShopware\Model;

class ProductAttributeData
{
    private const ATTRIBUTE_FILE  = 'FileAttribute';
    private const ATTRIBUTE_IMAGE = 'ImageAttribute';

    private array $translations;

    public function __construct(array $data)
    {
        $this->buildTranslations($data);
    }

    public function getTranslation(string $language): mixed
    {
        return $this->translations[$language] ?? null;
    }

    private function buildTranslations(array $data): void
    {
        foreach ($data['translations'] ?? [] as $translation) {
            switch ($data['attribute']['__typename']) {
                case self::ATTRIBUTE_IMAGE:
                    $this->translations[$translation['language']] = new ProductImageData(
                        $translation['value_multimedia']['name'],
                        $translation['value_multimedia']['extension'],
                        $translation['value_multimedia']['mime'],
                        $translation['value_multimedia']['size'],
                        $translation['value_multimedia']['url']
                    );
                    break;
                case self::ATTRIBUTE_FILE:
                    foreach ($translation['value_multimedia_array'] as $translationRecord) {
                        $this->translations[$translation['language']][] = new ProductFileData(
                            $translationRecord['name'],
                            $translationRecord['extension'],
                            $translationRecord['mime'],
                            $translationRecord['size'],
                            $translationRecord['url']
                        );
                    }
                    break;
            }
        }
    }
}
