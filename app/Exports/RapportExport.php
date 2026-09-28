<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Color;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RapportExport implements FromCollection, ShouldAutoSize, WithStyles
{
    protected $totalVentes;

    protected $totalProduits;

    protected $totalLivraison;

    protected $totalCommandes;

    protected $topArticles;

    protected $topClients;

    protected $dateDebut;

    protected $dateFin;

    public function __construct($totalVentes, $totalProduits, $totalLivraison, $totalCommandes, $topArticles, $topClients, $dateDebut, $dateFin)
    {
        $this->totalVentes = $totalVentes;
        $this->totalProduits = $totalProduits;
        $this->totalLivraison = $totalLivraison;
        $this->totalCommandes = $totalCommandes;
        $this->topArticles = $topArticles;
        $this->topClients = $topClients;
        $this->dateDebut = $dateDebut;
        $this->dateFin = $dateFin;
    }

    public function collection()
    {
        $data = [];

        // EN-TÊTE DU DOCUMENT (Lignes 1 à 4)
        $data[] = ['RAPPORT D\'ACTIVITÉ COMMERCIAL - CLAIREAFRIQUE'];
        $data[] = ['Généré le', now()->format('d/m/Y à H:i')];
        $data[] = ['Période analysée', ($this->dateDebut ? date('d/m/Y', strtotime($this->dateDebut)) : 'Début').' au '.($this->dateFin ? date('d/m/Y', strtotime($this->dateFin)) : 'Aujourd\'hui')];
        $data[] = [''];

        // SECTION 1 : SYNTHÈSE FINANCIÈRE (Lignes 5 à 11)
        $data[] = ['1. SYNTHÈSE GLOBALE FINANCIÈRE'];
        $data[] = ['Indicateur', 'Nombre / Montant'];
        $data[] = ['Nombre total de commandes', $this->totalCommandes];
        $data[] = ['Chiffre d\'affaires Ventes Produits', number_format((float) $this->totalProduits, 0, ',', ' ').' F CFA'];
        $data[] = ['Total Frais de Livraison perçus', number_format((float) $this->totalLivraison, 0, ',', ' ').' F CFA'];
        $data[] = ['CHIFFRE D\'AFFAIRES TOTAL GÉNÉRAL', number_format((float) $this->totalVentes, 0, ',', ' ').' F CFA'];
        $data[] = [''];

        // SECTION 2 : TOP ARTICLES (Lignes 12 à ...)
        $data[] = ['2. VENTES PAR ARTICLE (TOP ARTICLES)'];
        $data[] = ['Rang', 'Désignation de l\'article', 'Quantité vendue', 'Chiffre d\'affaires (F CFA)'];

        $rangArticle = 1;
        foreach ($this->topArticles as $a) {
            $data[] = [
                '#'.$rangArticle++,
                $a->designation,
                $a->total_vendu,
                number_format((float) $a->chiffre_affaires, 0, ',', ' ').' F CFA',
            ];
        }
        $data[] = [''];

        // SECTION 3 : TOP CLIENTS
        $data[] = ['3. CLASSEMENT DES MEILLEURS CLIENTS'];
        $data[] = ['Rang', 'Nom', 'Prénom', 'Nombre de commandes', 'Total dépensé (F CFA)'];

        $rangClient = 1;
        foreach ($this->topClients as $c) {
            $data[] = [
                '#'.$rangClient++,
                $c->nom,
                $c->prenom,
                $c->commandes_count,
                number_format((float) $c->total_depense, 0, ',', ' ').' F CFA',
            ];
        }

        return new Collection($data);
    }

    public function styles(Worksheet $sheet)
    {
        $nbArticles = count($this->topArticles);

        // 1. Grand titre (Ligne 1)
        $sheet->mergeCells('A1:E1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14)->setColor(new Color('FFFFFF'));
        $sheet->getStyle('A1')->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('1E3A8A'); // Bleu Nuit
        $sheet->getStyle('A1')->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // 2. Calcul dynamique des lignes
        $rowSec1 = 5;  // Section 1 : Synthèse
        $rowHdr1 = 6;  // En-tête Synthèse
        $rowTotal = 10; // Total Général

        $rowSec2 = 12; // Section 2 : Top Articles
        $rowHdr2 = 13; // En-tête Top Articles

        $rowSec3 = 15 + $nbArticles; // Section 3 : Top Clients
        $rowHdr3 = 16 + $nbArticles; // En-tête Top Clients

        // 3. Application des styles aux titres de sections
        foreach ([$rowSec1, $rowSec2, $rowSec3] as $row) {
            $sheet->getStyle("A{$row}:E{$row}")->getFont()->setBold(true)->setSize(11)->setColor(new Color('1E3A8A'));
        }

        // 4. Application des styles aux en-têtes de tableaux
        foreach ([$rowHdr1, $rowHdr2, $rowHdr3] as $row) {
            $sheet->getStyle("A{$row}:E{$row}")->getFont()->setBold(true);
            $sheet->getStyle("A{$row}:E{$row}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('E2E8F0');
        }

        // 5. Mise en évidence de la ligne "Chiffre d'affaires Total"
        $sheet->getStyle("A{$rowTotal}:B{$rowTotal}")->getFont()->setBold(true);
        $sheet->getStyle("A{$rowTotal}:B{$rowTotal}")->getFill()->setFillType(Fill::FILL_SOLID)->getStartColor()->setRGB('FEF08A');

        return [];
    }
}
