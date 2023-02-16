<?php

declare(strict_types=1);

namespace salty\Sw6PerformanceAnalysis\Controller;

use phpDocumentor\Reflection\Element;
use salty\Sw6PerformanceAnalysis\Analyzer\Analyzer;
use salty\Sw6PerformanceAnalysis\Struct\ResultCollection;
use Shopware\Core\Checkout\Cart\Validator;
use Shopware\Core\Content\Sitemap\Struct\Url;
use Shopware\Core\Content\Test\Product\Repository\ProductRepositoryTest;
use Shopware\Core\DevOps\StaticAnalyze\PHPStan\Rules\FinalClassRule;
use Shopware\Core\Framework\Routing\Annotation\RouteScope;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Shopware\Core\Framework\DataAbstractionLayer\EntityRepositoryInterface;
use Shopware\Core\Framework\Context;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Criteria;
use Shopware\Core\Framework\DataAbstractionLayer\Search\Filter\EqualsFilter;

/**
 * @RouteScope(scopes={"api"})
 * @Route(path="/api/_action/salty-performance-analysis")
 */
class PerformanceAnalysis extends AbstractController
{
    /** @var iterable<Analyzer> */
    private $serverConfigAnalyzers;

    /** @var iterable<Analyzer> */
    private $shopwareConfigAnalyzers;

    private EntityRepositoryInterface $mediaRepository;

    private EntityRepositoryInterface $productRepository;

    private EntityRepositoryInterface $productMediaLink;

    public int $anzahl = 0;

    public function __construct(iterable $serverConfigAnalyzers, iterable $shopwareConfigAnalyzers, EntityRepositoryInterface $mediaRepository, EntityRepositoryInterface $productRepository, EntityRepositoryInterface $productMediaLink)
    {
        $this->serverConfigAnalyzers   = $serverConfigAnalyzers;
        $this->shopwareConfigAnalyzers = $shopwareConfigAnalyzers;
        $this->mediaRepository = $mediaRepository;
        $this->productRepository = $productRepository;
        $this->productMediaLink = $productMediaLink;
    }

    
    

    /**
     * @Route(path="/shopware-configuration", methods={"GET"}, name="api.salty.performance.analysis.shopware-configurations-information")
     */
    public function getShopwareConfigurationInformation(): JsonResponse
    {
        $result = new ResultCollection();

        foreach ($this->shopwareConfigAnalyzers as $analyzer) {
            $analyzer->analyze($result);
        }

        return new JsonResponse($result);
    }

    /**
     * @Route(path="/server-configuration", methods={"GET"}, name="api.salty.performance.analysis.server-configurations-information")
     */
    public function getServerConfigurationInformation(): JsonResponse
    {
        $result = new ResultCollection();

        foreach ($this->serverConfigAnalyzers as $analyzer) {
            $analyzer->analyze($result);
        }

        return new JsonResponse($result);
    }

    /**
     * @Route(path="/media-configuration", methods={"GET"}, name="api.salty.performance.analysis.media-configurations-information")
     */
    public function getMediaConfigurationInformation(Context $context): JsonResponse
    {
        $criteria = new Criteria();
        //$criteria->addFilter(new EqualsFilter('fileName', 'demostore-logo'));
        
        //Liste aller Produkte im Shop am Ende in products
        $products = $this->productRepository->search($criteria,$context)->getEntities()->getElements();

        //Liste aller Produkt-Media-Link Elementen
        $link = $this->productMediaLink->search($criteria,$context)->getEntities()->getElements();
        
        //Liste aller Mediendateien im Shop am Ende in media 
        $media = $this->mediaRepository->search($criteria, $context)->getEntities()->getElements();
        
        $productsWithCovers = [];
        $missingMediaIds = [];
        $productMediaLinkList = [];
        $missingMedia = [];
        $finalResult = [];
        
        //Erstellt in productsWithCovers eine Liste aller Produkte, die ein Cover haben
        foreach ($products as $b)
        {
            $c = $b->get("coverId");
            if ($c != NULL)
            array_push($productsWithCovers, $b);
        }

        //Erstellt Liste mit den Mediendateien, die nicht vorhanden sind in missingMedia. Außerdem werden die IDs dieser Elemente in missingMediaIds gepackt.
        foreach ($media as $value )
        {   
            $urlGanz = $value->get("url");
            $test = '.'.str_replace("http://localhost", "", $urlGanz);
            
            if (!is_file($test)){
                array_push($missingMedia, $value);
                array_push($missingMediaIds, $value->get("id")); 
            }
        }

        //Erstellt eine Liste in productMediaLinkList mit allen Link-Elementen, die ein Produkt mit einem fehlenden Bild verknüpfen
        foreach ($missingMediaIds as $id)
        {
            foreach ($link as $l)
            {
                $help = $l->get("mediaId");
                if ($id == $help)
                {
                    //array_push($productMediaLinkList, $l);
                    array_push($productMediaLinkList, $l);
                }
            }
        }

        //Erstellt neue Liste in finalResult, mit dem Namen, der ID, dem Path und der Product Number (der Produkte, die das Bild verwenden) der fehlerhaften Bilder
        foreach ($missingMedia as $m)
        {
            $array = 
                        [
                                "productName"=> "-",
                                "url"=> $m->get("url"),
                                "id"=> $m->get("id"),
                                "fileName"=> $m->get("fileName"),
                        ];
            array_push($finalResult, $array);
        }

        foreach ($productMediaLinkList as $p)
        {
            $pid = $p->get("productId");
            $mid = $p->get("mediaId");
            foreach ($missingMedia as $m)
            {
                $int = array_search($mid, $finalResult);
                if ($int >= 0)
                {
                    $pNr = "";
                    foreach ($productsWithCovers as $pW)
                    {
                        if ($pW->get("id") == $pid)
                        {
                            $pNr = $pNr . $pW->get("productNumber");
                            if (substr($pNr, strlen($pNr)-1) == "")
                            {
                                $pNr = $pNr .", ";
                            }
                            
                        }
                    }
                    if (substr($pNr, -1) == ",")
                    {
                        rtrim($pNr, ", ");
                    }
                    $before = array("-");
                    $finalResult[$int] = str_replace($before, $pNr, $finalResult[$int]);
                }
            }
        }
        
        
        /* $i = 0;
        foreach ($productMediaLinkList as $prod)
        {
            $prodMediaId = $prod->get("mediaId");
            foreach ($missingMedia as $res)
            {
                $resMediaId = $res->get("id");
                
                if ($resMediaId == $prodMediaId)
                {   
                    foreach ($products as $b)
                    if ($prod->get("productId") == $b->get("id"))
                    {
                        $h = $b->get("productNumber");
                        $array = 
                        [
                                "productName"=> $h,
                                "url"=> $res->get("url"),
                                "id"=> $res->get("id"),
                                "fileName"=> $res->get("fileName"),
                        ];
                        array_push($finalResult, $array);
                        //echo "$array";
                        $i++;
                    }
                }
                if ($i != 0)
                {
                    $i = 0;
                    continue;
                }
                $array = 
                        [
                                "productName"=> "None Linked",
                                "url"=> $res->get("url"),
                                "id"=> $res->get("id"),
                                "fileName"=> $res->get("fileName"),
                        ];
                        array_push($finalResult, $array);
                //array_push($finalResult, $res);
            }
        } */
        
/*         global $anzahl;
        $$anzahl = count($finalResult); */

        if ($finalResult == NULL)
            $finalResult = [
                [
                    "fileName"=>"Es sind keine fehlerhaften Mediendateien vorhanden",
                    "url"=>"nix",
                ],
            ];
            


        return new JsonResponse($finalResult);
    }
    
    /* public function getMediaId () */
    public function getCount (): int {
        return $$anzahl;
    }
}
