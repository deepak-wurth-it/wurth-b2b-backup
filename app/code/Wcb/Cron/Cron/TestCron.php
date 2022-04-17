<?php
namespace Wcb\Cron\Cron;
class TestCron
{
       public function execute()
       {
         $test = new \Zend\Log\Writer\Stream(BP . '/var/log/cron.log');
         $logger = new \Zend\Log\Logger();
         $logger->addWriter($test);
         $logger->info(__DIR__).PHP_EOL;
         $logger->info(__METHOD__).PHP_EOL;
          return $this;
        }
}
