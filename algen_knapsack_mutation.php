<?php
//Crossover
class Parameters
{
    const FILE_NAME = 'uastesi.txt';
    const COLUMNS = ['item', 'price'];
    const POPULATION_SIZE = 10;
    const BUDGET = 110000;
    const STOPING_VALUE = 1000;
    const CROSSOVER_RATE = 0.8;
}

class Catalogue //untuk membaca file txt produk
{

    function createProductColumn($listOfRawProduct){
        //membaca setiap kolom dari item produk dengan menggunakan key
        foreach (array_keys($listOfRawProduct) as $listOfRawProductKey){
            // mengganti index menjadi item dan price
            $listOfRawProduct[Parameters::COLUMNS[$listOfRawProductKey]] = $listOfRawProduct[$listOfRawProductKey];
            unset($listOfRawProduct[$listOfRawProductKey]);//mengosongkan kembali array yang sebelumnya
        }
        return $listOfRawProduct; //return value yang sudah berubah dari inex 0 1 menjadi item price
                                //dan akan disimpan di array collectionOfListProduct[]
    }
    // fungsi untuk memanggil file txt
    function product(){
        $collectionOfListProduct = [];

        $raw_data = file(Parameters::FILE_NAME);//memanggil nama file
        //membaca item tiap baris kemudian disimpan
        foreach ($raw_data as $listOfRawProduct){
             $collectionOfListProduct[] = $this->createProductColumn(explode(",", $listOfRawProduct));
        }
        // melihat katalog produk
        //foreach ($collectionOfListProduct as $listOfRawProduct){
        //   print_r($listOfRawProduct);
        //   echo '<br>';
        //}
        return $collectionOfListProduct;
    }
}
class Individu
{
    function countNumberofGen(){
        $catalogue = new Catalogue;
        return count($catalogue->product());
    }
    function createRandomIndividu(){
        //echo $this->countNumberofGen();exit();
        for($i = 0;$i <= $this->countNumberofGen()-1;$i++){
            $ret[] = rand(0,1);
        }
        return $ret;
    }
}
class Population //membuat populasi awal
{
    function createRandomPopulation(){
        $individu = new Individu;
        for ($i = 0; $i <= Parameters::POPULATION_SIZE-1; $i++){
           $ret[] = $individu -> createRandomIndividu();
        }
        return $ret;
        //foreach ($ret as $key => $val){
        //    print_r($val);
        //    echo '<br>';
        //}
    }
}

class Fitness
{
    function selectingItem($individu){
        $catalogue =  new Catalogue;
        foreach($individu as $individuKey => $binaryGen){
            if($binaryGen === 1){
                $ret[] = [
                    'selectedKey' => $individuKey,
                    'selectedPrice' => $catalogue->product()[$individuKey]['price']
                ];
            }
        }
        return $ret;
    }
    function calculateFitnessValue($individu){
        return array_sum(array_column($this->selectingItem($individu),'selectedPrice'));
    }
    function countSelectedItem($individu){
        return count($this->selectingItem($individu));
    }
    function searchBestIndividu($fits, $maxItem, $numberOfIndividuHasMaxItem){
        if($numberOfIndividuHasMaxItem === 1){
            $index = array_search($maxItem, array_column($fits, 'numberOfSelectedItem'));
            
            return $fits[$index];
            
        } else {
            foreach($fits as $key => $val){
                if($val['numberOfSelectedItem'] === $maxItem){
                    echo $key.' '.$val['fitnessValue'].'<br>';
                    $ret[] = [
                        'individuKey' => $key,
                        'fitnessValue' => $val['fitnessValue']
                    ];
                }
            }
            if(count(array_unique(array_column($ret, 'fitnessValue'))) === 1){
                $index = rand(0, count($ret) - 1);
            } else {
                $max = max(array_column($ret, 'fitnessValue'));
                $index = array_search($max, array_column($ret, 'fitnessValue'));
            }
            echo 'Hasil';
            return $ret[$index];
        }
    }
    function isFound($fits){
        $countedMaxItems = array_count_values(array_column($fits, 'numberOfSelectedItem'));
        print_r($countedMaxItems);
        echo '<br>';
        $maxItem = max(array_keys($countedMaxItems));
        echo $maxItem;
        echo '<br>';
        echo $countedMaxItems[$maxItem];
        $numberOfIndividuHasMaxItem = $countedMaxItems[$maxItem];
        
        $bestFitnessValue = $this->searchBestIndividu($fits, $maxItem, $numberOfIndividuHasMaxItem)['fitnessValue'];
        echo '<br>';
        echo '<br> Best Fitness Value: '.$bestFitnessValue;

        $residual = Parameters::BUDGET -$bestFitnessValue;
        echo ' Residual: '.$residual;

        if($residual<=Parameters::STOPING_VALUE && $residual > 0){
            return TRUE;
        }
    }
    function isFit($fitnessValue){
        if ($fitnessValue <= Parameters::BUDGET){
            return TRUE;
        }
    }
    function fitnessEvaluation($population){
        $catalogue = new Catalogue;
        foreach ($population as $listOfIndividuKey => $listOfIndividu){
            echo 'Individu-'.$listOfIndividuKey.'<br>';
            foreach ($listOfIndividu as $individuKey => $binaryGen){
                echo $binaryGen.'&nbsp;&nbsp;';
                print_r($catalogue->product()[$individuKey]);
                echo '<br>';
            }
            $fitnessValue = $this->calculateFitnessValue($listOfIndividu);
            $numberOfSelectedItem = $this->countSelectedItem($listOfIndividu);
            echo 'Max. Item: '.$numberOfSelectedItem;
            echo ' Fitness value: '.$fitnessValue;
            if ($this->isFit($fitnessValue)){
                echo '(Fit)';
                $fits[] = [
                    'selectedIndividuKey' => $listOfIndividuKey,
                    'numberOfSelectedItem' => $numberOfSelectedItem,
                    'fitnessValue' => $fitnessValue
                ];
                print_r($fits);
            } else {
                echo '(Not Fit)';
            }
            echo '<p>';
        }
        if($this->isFound($fits)){
            echo 'Found';
        }else {
            echo ' >>Next Generation';
        }
    }
}

class Crossover
{
    public $population;

    function __construct($population){
        $this->population = $population;
    }
    function randomZeroToOne(){
        return (float) rand() / (float) getrandmax();
    }
    function generateCrossover(){
        for($i = 0; $i <= Parameters::POPULATION_SIZE-1; $i++){
            $randomZeroToOne = $this->randomZeroToOne();
            if($randomZeroToOne < Parameters::CROSSOVER_RATE){
                $parents[$i] = $randomZeroToOne;
            }
        }
        foreach (array_keys($parents) as $key){
            foreach (array_keys($parents) as $subkey){
                if($key !== $subkey){
                    $ret[] = [$key, $subkey];
                }
            }
            array_shift($parents);
        }
        return $ret;
    }
    function offspring($parent1, $parent2, $cutPointIndex, $offspring){
        $lengthOfGen = new Individu;
        if ($offspring === 1){
            for ($i = 0; $i <= $lengthOfGen->countNumberofGen()-1; $i++){
                if ($i <= $cutPointIndex){
                    $ret[] = $parent1[$i];
                }
                if ($i > $cutPointIndex){
                    $ret[] = $parent2[$i];
                }
            }     
        }
        if ($offspring === 2){
            for ($i = 0; $i <= $lengthOfGen->countNumberofGen()-1; $i++){
                if ($i <= $cutPointIndex){
                    $ret[] = $parent2[$i];
                }
                if ($i > $cutPointIndex){
                    $ret[] = $parent1[$i];
                }
            }     
        }
        return $ret;
    }
    function cutPointRandom(){
        $lengthOfGen = new Individu;
        return rand(0, $lengthOfGen->countNumberofGen()-1);
    }
    function crossover(){
        $cutPointIndex = $this->cutPointRandom();
        
        foreach ($this->generateCrossover() as $listOfCrossover){
            $parent1 = $this->population[$listOfCrossover[0]];
            $parent2 = $this->population[$listOfCrossover[1]];
            
            $offspring1 = $this->offspring($parent1, $parent2, $cutPointIndex, 1);
            $offspring2 = $this->offspring($parent1, $parent2, $cutPointIndex, 2);
           
           $offsprings[] = $offspring1;
           $offsprings[] = $offspring2;
        }
        return $offsprings;
    }
}

class Randomizer{
    static function getRandomIndexOfGen(){
        return rand(0, (new Individu())->countNumberofGen()-1);
    }
    static function getRandomIndexOfIndividu(){
        return rand(0, Parameters::POPULATION_SIZE-1);
    }
}

class Mutation
{
    function __construct($population){
        $this->population = $population;
    }
    function calculateMutationRate(){
        return 1 / (new Individu())->countNumberofGen();
    }
    function calculateNumOfMutation(){
        return round($this->calculateMutationRate() * Parameters::POPULATION_SIZE);
    }
    function isMutation(){
        if($this->calculateNumOfMutation() > 0){
            return TRUE;
        }
    }
    function generateMutation($valueOfGen){
        if($valueOfGen === 0){
            return 1;
        }else{
            return 0;
        }
    }
    function mutation(){
        if($this->isMutation()){
            for ($i = 0; $i <=$this->calculateNumOfMutation()-1; $i++) {
                $indexOfIndividu = Randomizer::getRandomIndexOfIndividu();
                $indexOfGen = Randomizer::getRandomIndexOfGen();
                $selectedIndividu = $this->population[$indexOfIndividu];
                
                echo 'Before mutation: ';
                print_r($selectedIndividu);
                echo '<br>';        
                $valueOfGen = $selectedIndividu[$indexOfGen];
                $mutatedGen = $this->generateMutation($valueOfGen);
                $selectedIndividu[$indexOfGen] = $mutatedGen;
                echo 'After mutation: ';
                print_r($selectedIndividu);
                echo '<br>'; 
                $ret[] = $selectedIndividu;
            }
            return $ret;
        }
    }
}

$initialPopulation = new Population;
$population = $initialPopulation->createRandomPopulation();

//$fitness = new Fitness;
//$fitness->fitnessEvaluation($population);

$crossover = new Crossover($population);
$crossoverOffspring = $crossover->crossover();

echo 'Crossover offspring:<br>';
print_r($crossoverOffspring);

echo '<p></p>';
$mutation = new Mutation($population);
if($mutation->mutation()){
    $mutationOffspring = $mutation->mutation();
    echo 'Mutation offspring:<br>';
    print_r($mutationOffspring);
    echo'<p></p>';
    foreach($mutationOffspring as $mutationOffspring){
        $crossoverOffspring[] = $mutationOffspring;
    }
}
echo 'Mutation offsprings <br>';
print_r($crossoverOffspring);
?>