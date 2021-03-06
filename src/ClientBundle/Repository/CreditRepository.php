<?php

declare(strict_types=1);

/*
 * This file is part of SolidInvoice project.
 *
 * (c) 2013-2017 Pierre du Plessis <info@customscripts.co.za>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace SolidInvoice\ClientBundle\Repository;

use SolidInvoice\ClientBundle\Entity\Client;
use SolidInvoice\ClientBundle\Entity\Credit;
use Doctrine\ORM\EntityRepository;
use Money\Money;

class CreditRepository extends EntityRepository
{
    /**
     * @param Client $client
     * @param Money  $amount
     *
     * @return Credit
     */
    public function addCredit(Client $client, Money $amount): Credit
    {
        $credit = $client->getCredit();

        $credit->setValue($credit->getValue()->add($amount));

        return $this->save($credit);
    }

    /**
     * @param Client $client
     * @param Money  $amount
     *
     * @return \SolidInvoice\ClientBundle\Entity\Credit
     */
    public function deductCredit(Client $client, Money $amount): Credit
    {
        $credit = $client->getCredit();

        $credit->setValue($credit->getValue()->subtract($amount));

        return $this->save($credit);
    }

    /**
     * @param Credit $credit
     *
     * @return Credit
     */
    private function save(Credit $credit): Credit
    {
        $entityManager = $this->getEntityManager();
        $entityManager->persist($credit);
        $entityManager->flush();

        return $credit;
    }

    /**
     * @param Client $client
     */
    public function updateCurrency(Client $client)
    {
        $filters = $this->getEntityManager()->getFilters();
        $filters->disable('archivable');
        $filters->disable('softdeleteable');

        $qb = $this->createQueryBuilder('c');

        $qb->update()
            ->set('c.value.currency', ':currency')
            ->where('c.client = :client')
            ->setParameter('currency', $client->getCurrency())
            ->setParameter('client', $client);

        $qb->getQuery()->execute();

        $filters->enable('archivable');
        $filters->enable('softdeleteable');
    }
}
