<?php

namespace Tests\Unit;

use App\Rules\MotDePasseComplexe;
use PHPUnit\Framework\TestCase;

/**
 * Couvre chaque branche de App\Rules\MotDePasseComplexe : longueur
 * minimale, minuscule, majuscule, chiffre, caractère spécial — et le cas
 * valide qui ne doit déclencher aucun échec.
 */
class MotDePasseComplexeTest extends TestCase
{
    private function echecs(string $motDePasse): array
    {
        $echecs = [];
        (new MotDePasseComplexe)->validate('motDePasse', $motDePasse, function (string $message) use (&$echecs) {
            $echecs[] = $message;
        });

        return $echecs;
    }

    public function test_un_mot_de_passe_valide_ne_produit_aucun_echec(): void
    {
        $this->assertSame([], $this->echecs('Motdepasse1!'));
    }

    public function test_refuse_un_mot_de_passe_trop_court(): void
    {
        $echecs = $this->echecs('Ab1!');
        $this->assertNotEmpty($echecs);
        $this->assertStringContainsString('8 caractères', $echecs[0]);
    }

    public function test_refuse_labsence_de_minuscule(): void
    {
        $echecs = $this->echecs('MOTDEPASSE1!');
        $this->assertNotEmpty($echecs);
        $this->assertStringContainsString('minuscule', $echecs[0]);
    }

    public function test_refuse_labsence_de_majuscule(): void
    {
        $echecs = $this->echecs('motdepasse1!');
        $this->assertNotEmpty($echecs);
        $this->assertStringContainsString('majuscule', $echecs[0]);
    }

    public function test_refuse_labsence_de_chiffre(): void
    {
        $echecs = $this->echecs('Motdepasse!');
        $this->assertNotEmpty($echecs);
        $this->assertStringContainsString('chiffre', $echecs[0]);
    }

    public function test_refuse_labsence_de_caractere_special(): void
    {
        $echecs = $this->echecs('Motdepasse1');
        $this->assertNotEmpty($echecs);
        $this->assertStringContainsString('caractère spécial', $echecs[0]);
    }

    public function test_ne_sarrete_qua_la_premiere_regle_violee(): void
    {
        // Trop court ET aucune majuscule : seule l'erreur de longueur
        // doit remonter (chaque `return` après `$fail` coupe la suite).
        $echecs = $this->echecs('ab1!');
        $this->assertCount(1, $echecs);
        $this->assertStringContainsString('8 caractères', $echecs[0]);
    }
}
