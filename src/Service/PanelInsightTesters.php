<?php

namespace App\Service;

use App\Entity\Panel;
use App\Repository\TesterRepository;
use Symfony\Component\HttpFoundation\Request;

class PanelInsightTesters
{
    private $testerRepository;


    public function __construct(TesterRepository $testerRepository)
    {
        $this->testerRepository=$testerRepository;

    }
    public function getFilters(Panel $panel){
        $filters=[];
        $filtersBuIndexNumber=[];

        if(!is_null($panel->getGender())){
            $filters['gender']=$panel->getGender();
            $filtersBuIndexNumber[]=$panel->getGender();
        }
        if(!is_null($panel->getOs())){
            $filters['os']=$panel->getOs();
            $filtersBuIndexNumber[]=$panel->getOs();
        }
        if(!is_null($panel->getCsp())){
            $filters['csp']=$panel->getCsp();
            $filtersBuIndexNumber[]=$panel->getCsp();
        }
        if(!is_null($panel->getStudyLevel())){
            $filters['studyLevel']=$panel->getStudyLevel();
            $filtersBuIndexNumber[]=$panel->getStudyLevel();
        }
        if(!is_null($panel->getCountry())){
            $filters['country']=$panel->getCountry();
            $filtersBuIndexNumber[]=$panel->getCountry();
        }
//        if(!is_null($minAge)){
//            $sql['minAge']=$minAge;
//        }
//        if(!is_null($maxAge)){
//            $sql['maxAge']=$maxAge;
//        }
        return [$filters,$filtersBuIndexNumber];
    }

    public function selectInsightTestersParFiltres(Panel $panel){
        $nbTesters = $panel->getTestersNb();
        $filters = $this->getFilters($panel)[0];
        $filtersBuIndexNumber = $this->getFilters($panel)[1];
        $testers = $this->testerRepository->getRandomTestersByFiltres($filters,$nbTesters);
        if(count($testers)!=$nbTesters*5){
            if( isset($filters)){
               switch(count($filters)){
                   case 0:
                       $resultat='Il n\'y a pas assez de répondants correspond au filtre sélectionné.
                   Veuillez modifier le nombre de testeurs demandés';
                       break;
                   case 1:
                       $resultat='Il n\'y a pas assez de répondants correspond au filtre sélectionné.
                   Veuillez modifier vos critères, ou supprimer le filtre '.$filtersBuIndexNumber[0];
                       break;
                   case 2:
                       $filter1=$filters;
                       $filter2=$filters;
                       $i=1;
                       foreach($filters as $key=>$filter){
                           $x='filter'.$i;
                           unset($$x[$key]);
                           $i++;

                       }
                       $testers1=$this->testerRepository->getRandomTestersByFiltres($filter1,$nbTesters);
                       $testers2=$this->testerRepository->getRandomTestersByFiltres($filter2,$nbTesters);
                       if(count($testers1)>count($testers2)){
                           $resultat='Il n\'y a pas assez de répondants correspond aux filtres sélectionnés.
                   Veuillez modifier vos critères, ou supprimer le filtre '.$filtersBuIndexNumber[1];
                       }elseif (count($testers2)>count($testers1)){
                           $resultat='Il n\'y a pas assez de répondants correspond aux filtres sélectionnés.
                   Veuillez modifier vos critères, ou supprimer le filtre '.$filtersBuIndexNumber[0];
                       }else{
                           $resultat='Il n\'y a pas assez de répondants correspond aux filtres sélectionnés.
                   Veuillez modifier vos critères.';
                       }
                       break;
                   case 3:
                       $filter1=$filters;
                       $filter2=$filters;
                       $filter3=$filters;
                      $i=1;
                      foreach($filters as $key=>$filter){
                          $x='filter'.$i;
                          unset($$x[$key]);
                          $i++;

                      }
                       $testers1=$this->testerRepository->getRandomTestersByFiltres($filter1,$nbTesters);
                       $testers2=$this->testerRepository->getRandomTestersByFiltres($filter2,$nbTesters);
                       $testers3=$this->testerRepository->getRandomTestersByFiltres($filter3,$nbTesters);

                       if(count($testers1)>=count($testers2)){
                           $max[1]=count($testers1);
                           if(count($testers3)>$max){
                               $max[3]=count($testers3);
                           }

                       }else{
                           $max[2]=count($testers2);
                           if(count($testers3)>$max){
                               $max[3]=count($testers3);

                           }
                       }
                       foreach($max as $key=>$value){
                           if (isset($value)){
                               switch($key){
                                   case 1:
                                       if($testers2>=$testers3){
                                           $resultat='Il n\'y a pas assez de répondants correspond aux filtres sélectionnés.
                   Veuillez modifier vos critères, ou supprimer le filtre '.$filtersBuIndexNumber[1];
                                       }else $resultat='Il n\'y a pas assez de répondants correspond aux filtres sélectionnés.
                   Veuillez modifier vos critères, ou supprimer le filtre '.$filtersBuIndexNumber[2];
                                       break;
                                   case 2:
                                       if($testers1>=$testers3){
                                           $resultat='Il n\'y a pas assez de répondants correspond aux filtres sélectionnés.
                   Veuillez modifier vos critères, ou supprimer le filtre '.$filtersBuIndexNumber[0];
                                       }else $resultat='Il n\'y a pas assez de répondants correspond aux filtres sélectionnés.
                   Veuillez modifier vos critères, ou supprimer le filtre '.$filtersBuIndexNumber[2];
                                       break;
                                   case 3:
                                       if($testers1>=$testers2){
                                           $resultat='Il n\'y a pas assez de répondants correspond aux filtres sélectionnés.
                   Veuillez modifier vos critères, ou supprimer le filtre '.$filtersBuIndexNumber[0];
                                       }else $resultat='Il n\'y a pas assez de répondants correspond aux filtres sélectionnés.
                   Veuillez modifier vos critères, ou supprimer le filtre '.$filtersBuIndexNumber[1];
                                       break;
                               }

                           }
                           }

                       break;
               }


            }else $resultat='Il n\'y a pas assez de répondants correspond au filtre sélectionné.
                   Veuillez modifier le nombre de testeurs demandés';

            return ($resultat);
        }else{
            return ($testers);
        }


    }
}