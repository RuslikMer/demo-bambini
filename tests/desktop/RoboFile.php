<?php
require_once 'vendor/autoload.php';

class RoboFile extends \Robo\Tasks
{
    use \Codeception\Task\MergeReports;
    use \Codeception\Task\SplitTestsByGroups;

    public function parallelSplitTests()
    {
        // Split your tests by files
        $this->taskSplitTestFilesByGroups(1)
            ->projectRoot('.')
            ->testsFrom(['tests/acceptance', 'tests/atomic', 'tests/payments'])
            ->groupsTo('tests/_data/paracept_')
            ->run();
    }

    public function parallelRun($suites)
    {
        $result = '';
        $parallel = $this->taskParallelExec();
        //$tests = file("tests/_data/paracept_1");

        /*foreach ($suites as $suite) {
            //for ($i = 1; $i <= 2; $i++) {
                foreach ($tests as $test) {
                    if (str_contains($test, $suite)) {
                        unset($tests[array_search($test, $tests)]);
                        $test = str_replace('\\', '', strrchr($test, '\\'));
                        $parallel->process(
                            $this->taskCodecept()// use built-in Codecept task
                                ->option("--steps")
                                ->option("--debug")
                                ->env("env")
                                //->group("paracept")// for all paracept_* groups
                                ->suite($suite)// run tests
                                ->test($test)
                                //->xml($suite."result_$i.xml") // save XML results
                        );
                    }
                }
            //}
        }*/


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

        //$this->parallelSplitTests();
        $result = $this->parallelRun($suites);
        //$this->parallelMergeResults($suites);

        return $result;
    }
}