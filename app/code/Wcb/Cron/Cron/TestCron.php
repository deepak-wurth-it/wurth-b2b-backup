<?php
namespace Wcb\Cron\Cron;
class TestCron
{
       public function execute()
       {
         $test = new \Zend\Log\Writer\Stream(BP . '/var/log/cron.log');
         $logger = new \Zend\Log\Logger();
         $logger->addWriter($test);
         $logger->info('**************************************** Test Cron Executed ***********************************************');     
         $logger->info(__METHOD__);
         $logger->info('**************************************** End Test Cron  ***********************************************');     

          return $this;
        }
}
