<?php

namespace Crm\SagePayModule\Seeders;

use Crm\ApplicationModule\Seeders\ISeeder;
use Crm\PaymentsModule\Repository\PaymentGatewaysRepository;
use Symfony\Component\Console\Output\OutputInterface;

class PaymentGatewaysSeeder implements ISeeder
{
    private $paymentGatewaysRepository;
    
    public function __construct(PaymentGatewaysRepository $paymentGatewaysRepository)
    {
        $this->paymentGatewaysRepository = $paymentGatewaysRepository;
    }

    public function seed(OutputInterface $output)
    {
        if (!$this->paymentGatewaysRepository->exists('sagepay')) {
            $this->paymentGatewaysRepository->add(
                'SagePay',
                'sagepay',
                10,
                true
            );
            $output->writeln('  <comment>* payment gateway <info>sagepay</info> created</comment>');
        } else {
            $output->writeln('  * payment gateway <info>sagepay</info> exists');
        }

        if (!$this->paymentGatewaysRepository->exists('sagepay_reference')) {
            $this->paymentGatewaysRepository->add(
                'SagePay Reference',
                'sagepay_reference',
                15,
                true,
                true
            );
            $output->writeln('  <comment>* payment gateway <info>sagepay</info> created</comment>');
        } else {
            $output->writeln('  * payment gateway <info>sagepay</info> exists');
        }
    }
}
