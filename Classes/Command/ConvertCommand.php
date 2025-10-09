<?php

declare(strict_types=1);

namespace Monosize\Adventcalendar\Command;

use Monosize\Adventcalendar\Domain\Model\Prize;
use Monosize\Adventcalendar\Domain\Model\Sponsor;
use Monosize\Adventcalendar\Domain\Repository\CalendarRepository;
use Monosize\Adventcalendar\Domain\Repository\PrizeRepository;
use Monosize\Adventcalendar\Domain\Repository\SponsorRepository;
use Monosize\Entity\Domain\Model\Entity;
use Monosize\Entity\Domain\Repository\EntityRepository;
use Monosize\Newsblog\Utility\TextUtility;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Resource\StorageRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Repository\CategoryRepository;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

/**
 * Command for converting legacy AdventCalendar data
 */
class ConvertCommand extends Command
{
    /**
     * @var string
     */
    protected static $extKey = 'adventcalendar';

    protected function configure(): void
    {
        $this->setName('adventcalendar:convert');
        $this->setDescription('Convert legacy AdventCalendar data');
        $this->setHelp('Converts legacy AdventCalendar sponsors and prizes data to new format.');
        
        $this->addArgument(
            'type',
            InputArgument::REQUIRED,
            'Type of conversion: sponsors or prizes'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $type = $input->getArgument('type');

        try {
            switch ($type) {
                case 'sponsors':
                    return $this->convertSponsors($io);
                case 'prizes':
                    return $this->convertPrizes($io);
                default:
                    $io->error("Unknown conversion type: {$type}. Available types: sponsors, prizes");
                    return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $io->error("Error: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Convert sponsors from legacy format
     */
    protected function convertSponsors(SymfonyStyle $io): int
    {
        $io->title('Converting legacy sponsors');

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_gepartner_item');
        $results = $queryBuilder
            ->select('*')
            ->from('tx_gepartner_item')
            ->executeQuery()
            ->fetchAllAssociative();

        if (empty($results)) {
            $io->warning('No legacy sponsor data found in tx_gepartner_item table');
            return Command::SUCCESS;
        }

        $converted = 0;
        foreach ($results as $result) {
            $uid = $result['uid'];
            $dam = $this->getDam($uid);
            $file = null;
            
            if (\is_array($dam) && isset($dam['file_name'])) {
                $filename = $dam['file_name'];
                try {
                    $file = $this->findFile($filename);
                } catch (\Exception $e) {
                    $io->note("File not found: {$filename}");
                }
            }
            
            $entity = $this->createEntity($result, 129, $file);
            if ($entity) {
                $converted++;
                $io->writeln("Converted sponsor: {$result['name']}");
            }
        }

        $io->success("Converted {$converted} sponsors successfully");
        return Command::SUCCESS;
    }

    /**
     * Convert prizes from legacy format
     */
    protected function convertPrizes(SymfonyStyle $io): int
    {
        $io->title('Converting legacy prizes');

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_gegewinne_item');
        $sponsorResults = $queryBuilder
            ->select('*')
            ->from('tx_gegewinne_item')
            ->where($queryBuilder->expr()->eq('pid', 1572))
            ->orderBy('tag', 'ASC')
            ->groupBy('sponsor')
            ->executeQuery()
            ->fetchAllAssociative();

        if (empty($sponsorResults)) {
            $io->warning('No legacy prize data found in tx_gegewinne_item table');
            return Command::SUCCESS;
        }

        $entityRepository = GeneralUtility::makeInstance(EntityRepository::class);
        $calendarRepository = GeneralUtility::makeInstance(CalendarRepository::class);
        $calendar = $calendarRepository->findByUid(1);
        
        if (!$calendar) {
            $io->error('Calendar with ID 1 not found');
            return Command::FAILURE;
        }

        $prizeRepository = GeneralUtility::makeInstance(PrizeRepository::class);
        $sponsorRepository = GeneralUtility::makeInstance(SponsorRepository::class);
        $persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);
        
        $sponsorCounter = 0;
        foreach ($sponsorResults as $sponsorResult) {
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getQueryBuilderForTable('tx_gegewinne_item');
            $results = $queryBuilder
                ->select('*')
                ->from('tx_gegewinne_item')
                ->where($queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('pid', 1572),
                    $queryBuilder->expr()->eq('sponsor', $queryBuilder->createNamedParameter($sponsorResult['sponsor']))
                ))
                ->executeQuery()
                ->fetchAllAssociative();

            $winningSponsor = new Sponsor();
            $query = $entityRepository->createQuery();
            $query->getQuerySettings()->setRespectStoragePage(false);
            
            $entity = $query->matching($query->equals('info', (string)$sponsorResult['sponsor']))
                ->execute()
                ->getFirst();
                
            if ($entity) {
                $winningSponsor->setEntity($entity);
            }
            
            $winningSponsor->setDay((int)$sponsorResult['tag']);
            $winningSponsor->setCalendar($calendar);
            $winningSponsor->setPid(128);
            
            $counter = 0;
            foreach ($results as $result) {
                $name = str_replace('""', '"', $result['name']);
                if (substr($name, -1) === '"') {
                    $name = substr($name, 0, -1);
                }
                $name = preg_replace('/[ ]+/', ' ', $name);
                $name = trim($name, ",.:;\"\' \t\n\r\0\x0B");
                
                $winningPrice = new Prize();
                $winningPrice->setSponsor($winningSponsor);
                $winningPrice->setWinningNumber($result['nummer']);
                $winningPrice->setName($name);
                $winningPrice->setPid(128);
                $winningSponsor->addPrize($winningPrice);
                $counter++;
                
                // Update original record with cleaned name
                $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                    ->getQueryBuilderForTable('tx_gegewinne_item');
                $queryBuilder->getRestrictions()->removeAll();
                $queryBuilder
                    ->update('tx_gegewinne_item')
                    ->set('name', $name)
                    ->where($queryBuilder->expr()->eq('uid', $result['uid']))
                    ->executeStatement();
            }
            
            $sponsorRepository->add($winningSponsor);
            $persistenceManager->persistAll();

            // Update sponsor prize count
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getQueryBuilderForTable('tx_adventcalendar_domain_model_sponsor');
            $queryBuilder->getRestrictions()->removeAll();
            $queryBuilder
                ->update('tx_adventcalendar_domain_model_sponsor')
                ->set('prizes', $counter)
                ->where($queryBuilder->expr()->eq('uid', $winningSponsor->getUid()))
                ->executeStatement();
                
            $sponsorCounter++;
            $io->writeln("Converted sponsor prizes for day: {$sponsorResult['tag']}");
        }
        
        // Update calendar sponsor count
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_adventcalendar_domain_model_calendar');
        $queryBuilder->getRestrictions()->removeAll();
        $queryBuilder
            ->update('tx_adventcalendar_domain_model_calendar')
            ->set('sponsors', $sponsorCounter)
            ->where($queryBuilder->expr()->eq('uid', 1))
            ->executeStatement();

        $io->success("Converted {$sponsorCounter} sponsor groups with their prizes successfully");
        return Command::SUCCESS;
    }

    /**
     * Find file in storage
     */
    protected function findFile(string $filename): ?\TYPO3\CMS\Core\Resource\File
    {
        $storageRepository = GeneralUtility::makeInstance(StorageRepository::class);
        $storage = $storageRepository->findByUid(1);
        $folder = $storage->getFolder('/files/Adventskalender/Sponsoren');

        return $storage->getFileInFolder($filename, $folder);
    }

    /**
     * Get DAM record for given UID
     */
    protected function getDam(int $uid): ?array
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_dam');

        $result = $queryBuilder
            ->select('*')
            ->from('tx_dam', 'd')
            ->leftJoin('d', 'tx_dam_mm_ref', 'r', 'd.uid = r.uid_local')
            ->where($queryBuilder->expr()->andX(
                $queryBuilder->expr()->eq('r.ident', $queryBuilder->createNamedParameter('tx_gepartner_pi1')),
                $queryBuilder->expr()->eq('r.tablenames', $queryBuilder->createNamedParameter('tx_gepartner_item')),
                $queryBuilder->expr()->eq('r.uid_foreign', $queryBuilder->createNamedParameter($uid))
            ))
            ->executeQuery()
            ->fetchAssociative();

        return $result ?: null;
    }

    /**
     * Create entity from legacy data
     */
    protected function createEntity(array $data, int $pid, ?\TYPO3\CMS\Core\Resource\File $file): ?Entity
    {
        $entityRepository = GeneralUtility::makeInstance(EntityRepository::class);
        $persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);
        $categoryRepository = GeneralUtility::makeInstance(CategoryRepository::class);
        
        $sponsorCategory = $categoryRepository->findByUid(33);
        $sponsor = GeneralUtility::makeInstance(Entity::class);
        $sponsor->setType(1);
        $sponsor->setZip('');
        $sponsor->setCity('');
        $sponsor->setPhone(TextUtility::convertPhoneNumber($data['telephone']));
        $sponsor->setWww($data['homepage']);
        $sponsor->setFax('');
        $sponsor->setEmail('');
        $sponsor->setCompanyTitle($data['name']);
        $sponsor->setStreet('');
        $sponsor->setPid($pid);
        $sponsor->setInfo($data['uid']);

        $addressInfoOriginal = $data['address'];
        $email = TextUtility::filterEmail($addressInfoOriginal);
        $phone = TextUtility::filterTelephone($addressInfoOriginal);
        $fax = TextUtility::filterTelefax($addressInfoOriginal);
        $city = TextUtility::filterZipCity($addressInfoOriginal);
        
        $addressInfo = TextUtility::removeEmail($addressInfoOriginal);
        $addressInfo = TextUtility::removeTelefax($addressInfo);
        $addressInfo = TextUtility::removeTelephone($addressInfo);
        $addressInfo = TextUtility::removeZipCity($addressInfo);

        $addressInfo = GeneralUtility::trimExplode(\chr(10), $addressInfo);
        $count = \count($addressInfo);
        if ($count === 1) {
            $addressInfo = GeneralUtility::trimExplode(',', $addressInfo[0]);
            $count = \count($addressInfo);
        }

        if ($city['zip'] !== '') {
            $sponsor->setZip(trim($city['zip']));
        }
        if ($city['city'] !== '') {
            $sponsor->setCity(trim($city['city']));
        }
        if ($phone !== '') {
            $sponsor->setPhone(TextUtility::convertPhoneNumber($phone));
        }
        if ($fax !== '') {
            $sponsor->setFax(TextUtility::convertPhoneNumber($fax));
        }
        if ($email !== '') {
            $sponsor->setEmail($email);
        }
        if ($count > 1) {
            $sponsor->setCompanyTitle(trim($addressInfo[0]));
            $sponsor->setStreet(trim($addressInfo[1]));
        } elseif ($count === 1) {
            $sponsor->setStreet(trim($addressInfo[0]));
        }
        
        if ($data['sponsor']) {
            $sponsor->addCategory($sponsorCategory);
        }

        $double = $entityRepository->findDoubleEntry($sponsor);
        if ($double === null) {
            $entityRepository->add($sponsor);
        } else {
            $sponsor = $double;
            $file = null;
        }

        $persistenceManager->persistAll();

        if ($file) {
            $fileReference = [
                'uid_local'   => $file->getUid(),
                'uid_foreign' => $sponsor->getUid(),
                'tablenames'  => 'tx_entity_domain_model_entity',
                'fieldname'   => 'company_images',
                'table_local' => 'sys_file',
                'tstamp'      => time(),
            ];
            
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getQueryBuilderForTable('sys_file_reference');
            $queryBuilder->insert('sys_file_reference')
                ->values($fileReference)
                ->executeStatement();
        }

        return $sponsor;
    }
}