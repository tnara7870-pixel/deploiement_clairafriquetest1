<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Levée quand une action du workflow d'annulation est demandée alors que
 * la commande n'est pas dans un état compatible (ex. valider une
 * annulation sans demande en attente, redemander une annulation déjà en
 * cours, enregistrer un remboursement sans annulation validée...).
 * Le message est déjà formulé pour être affiché tel quel à l'utilisateur.
 */
class CommandeAnnulationException extends RuntimeException {}
