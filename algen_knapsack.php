<?php

class Catalogue
{
    function createProductColumn($columns, $listOfRawProduct){
        foreach (array_keys($listOfRawProduct) as $listOfRawProductProductKey){
            $listOfRawProduct[$columns[$listOfRawProductProductKey]] = $listOfRawProduct[$listOfRawProductProductKey];
            unset($listOfRawProduct[$listOfRawProductProductKey]);
        }
        return $listOfRawProduct;
    }

    function product($parameters){
        $collectionOfListProducts = [];

        $raw_data = file($parameters['file_name']);
        foreach ($raw_data as $listOfRawProduct){
            $collectionOfListProduct[] = $this->createProductColumn($parameters['columns'], explode(",", $listOfRawProduct));
        }
        
        return [
            'product' => $collectionOfListProduct,
            'gen_length' => count($collectionOfListProduct)
        ];
    }
}

class PopulationGenerator
{
    function createIndividu($parameters){
        $catalogue = new Catalogue;
        $lengthOfGen = $catalogue->product($parameters)['gen_length']-1;
        for ($i = 0; $i <= $lengthOfGen-1; $i++){
            $ret[] = rand(0, 1);
        }
        return $ret;
    }

    function createPopulation($parameters){
        for ($i = 0; $i <= $parameters['population_size']; $i++){
            $ret[] = $this->createIndividu($parameters);
        }
        foreach ($ret as $key => $val){
            print_r($val);
            echo '<br>';
        }
    }
}

$parameters = [
    'file_name' => 'uastesi.txt',
    'columns' => ['item', 'price'],
    'population_size' => 9
];

$katalog = new Catalogue;
$katalog->product($parameters);

$initialPopulation = new PopulationGenerator;
$initialPopulation->createPopulation($parameters);