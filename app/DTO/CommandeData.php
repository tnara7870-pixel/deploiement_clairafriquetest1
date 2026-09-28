<?php

namespace App\DTO;

use App\Http\Requests\PreparerCommandeRequest;

class CommandeData
{
    public function __construct(
        public int $userId,
        public string $modeLivraison,
        public ?string $adresse,
        public ?string $pointVente,
        public ?float $latitude,
        public ?float $longitude,
        public string $modePaiement,
        public float $total,
        public float $fraisLivraison,
    ) {}

    public static function fromArray(array $data, int $userId): self
    {
        return new self(
            userId: $userId,
            modeLivraison: $data['modeLivraison'] ?? '',
            adresse: $data['adresse'] ?? null,
            pointVente: $data['pointVente'] ?? null,
            latitude: isset($data['latitude']) ? (float) $data['latitude'] : null,
            longitude: isset($data['longitude']) ? (float) $data['longitude'] : null,
            modePaiement: $data['modePaiement'] ?? 'wave',
            total: (float) ($data['total'] ?? 0),
            fraisLivraison: (float) ($data['fraisLivraison'] ?? 0),
        );
    }

    public static function fromRequest(PreparerCommandeRequest $request, int $userId, float $total, float $fraisLivraison): self
    {
        return new self(
            userId: $userId,
            modeLivraison: $request->input('modeLivraison'),
            adresse: $request->input('adresse'),
            pointVente: $request->input('pointVente'),
            latitude: $request->filled('latitude') ? (float) $request->input('latitude') : null,
            longitude: $request->filled('longitude') ? (float) $request->input('longitude') : null,
            modePaiement: $request->input('modePaiement'),
            total: $total,
            fraisLivraison: $fraisLivraison,
        );
    }

    public function toArray(): array
    {
        return [
            'modeLivraison' => $this->modeLivraison,
            'adresse' => $this->adresse,
            'pointVente' => $this->pointVente,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'modePaiement' => $this->modePaiement,
            'total' => $this->total,
            'fraisLivraison' => $this->fraisLivraison,
        ];
    }
}
