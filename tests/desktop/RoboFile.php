<?php
require_once 'vendor/autoload.php';

class RoboFile extends \Robo\Tasks
{
    use \Codeception\Task\MergeReports;
    use \Codeception\Task\SplitTestsByGroups;

    public function parallelRun($suites)
    {
        $result = '';
        $parallel = $this->taskParallelExec();

        foreach ($suites as $suite) {
            for ($i = 1; $i <= 2; $i++) {
                $parallel->process(
                    $this->taskCodecept()// use built-in Codecept task
                        ->suite($suite)// run tests
                        ->env("browserstack")
                        //->options(["--steps", "--debug"])
                        ->group("paracept_$i")// for all paracept_* groups
                        //->xml($suite."result_$i.xml") // save XML results
                );
            }
            $result = $parallel->run();
        }

        return $result;
    }

    public function parallelMergeResults($suites)
    {
        $merge = $this->taskMergeXmlReports();

        foreach ($suites as $suite) {
            for ($i = 1; $i <= 5; $i++) {
                $merge->from("tests/_output/" . $suite . "result_$i.xml");
            }
        }

        $merge->into("tests/_output/result_paracept.xml")->run();
    }

    function parallelAll()
    {
        $suites = ['acceptance', 'atomic', 'payments'];

        $result = $this->parallelRun($suites);
        //$this->parallelMergeResults($suites);

        return $result;
    }
}