<?php

namespace App\Utils;

class ColumnProvider
{
    private $columnHeaders = ["Nom du Test", "N° Etape", "Nom","Prénom","Email", "Type de Question", "Question", "Réponse Testeur","Commentaire","Libellé Borne Basse","Libellé Borne Haute","MIN","MAX"];


    public function getColumn()
    {
        return $this->columnHeaders;
    }
}