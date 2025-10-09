<?php

declare(strict_types=1);

namespace Monosize\Adventcalendar\Command;

use Monosize\Adventcalendar\Domain\Model\Prize;
use Monosize\Adventcalendar\Domain\Model\Sponsor;
use Monosize\Adventcalendar\Domain\Repository\CalendarRepository;
use Monosize\Adventcalendar\Domain\Repository\PrizeRepository;
use Monosize\Adventcalendar\Domain\Repository\SponsorRepository;
use Monosize\Entity\Domain\Repository\EntityRepository;
use League\Csv\Reader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

/**
 * Command for AdventCalendar data import and management
 */
class CalendarCommand extends Command
{
    /**
     * @var string
     */
    protected static $extKey = 'adventcalendar';

    protected function configure(): void
    {
        $this->setName('adventcalendar:calendar');
        $this->setDescription('AdventCalendar data management commands');
        $this->setHelp('Provides commands for importing and managing AdventCalendar data from CSV files.');
        
        $this->addArgument(
            'action',
            InputArgument::REQUIRED,
            'Action to perform: prepare, update, or import'
        );
        
        $this->addArgument(
            'calendar-id',
            InputArgument::OPTIONAL,
            'Calendar ID (required for prepare action)'
        );
        
        $this->addArgument(
            'csv-file',
            InputArgument::OPTIONAL,
            'Path to CSV file (required for prepare and update actions)'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $action = $input->getArgument('action');

        try {
            switch ($action) {
                case 'prepare':
                    return $this->prepareCalendar($input, $io);
                case 'update':
                    return $this->updateCalendar($input, $io);
                case 'import':
                    return $this->importCalendar($io);
                default:
                    $io->error("Unknown action: {$action}. Available actions: prepare, update, import");
                    return Command::FAILURE;
            }
        } catch (\Exception $e) {
            $io->error("Error: " . $e->getMessage());
            return Command::FAILURE;
        }
    }

    /**
     * Prepare calendar from CSV file
     */
    protected function prepareCalendar(InputInterface $input, SymfonyStyle $io): int
    {
        $calId = $input->getArgument('calendar-id');
        $csvFile = $input->getArgument('csv-file');

        if (!$calId || !$csvFile) {
            $io->error('Calendar ID and CSV file path are required for prepare action');
            return Command::FAILURE;
        }

        if (!file_exists($csvFile)) {
            $io->error("CSV file not found: {$csvFile}");
            return Command::FAILURE;
        }

        $io->title('Preparing calendar from CSV');
        
        $calendarRepository = GeneralUtility::makeInstance(CalendarRepository::class);
        $calendar = $calendarRepository->findByUid((int)$calId);
        
        if (!$calendar) {
            $io->error("Calendar with ID {$calId} not found");
            return Command::FAILURE;
        }

        $reader = Reader::createFromPath($csvFile, 'r');
        $reader->setDelimiter(';');
        $reader->setHeaderOffset(0);
        $records = $reader->getRecords();
        $persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);
        $entityRepository = GeneralUtility::makeInstance(EntityRepository::class);
        $sponsorRepository = GeneralUtility::makeInstance(SponsorRepository::class);

        $processed = 0;
        foreach ($records as $offset => $record) {
            $sponsor = $sponsorRepository->existsSponsor((int)$calId, $record['entity'], $record['tag']);

            $prize = new Prize();
            $prize->setPid(129);
            $prize->setName($record['gewinn']);

            if (!$sponsor) {
                $io->writeln('Creating new sponsor...');
                $query = $entityRepository->createQuery();
                $query->getQuerySettings()->setRespectStoragePage(false);
                $entity = $query->matching($query->equals('uid', $record['entity']))->execute()->getFirst();
                
                if ($entity) {
                    $io->writeln('Entity found');
                } else {
                    $io->warning("Entity {$record['entity']} not found");
                    continue;
                }

                $sponsor = new Sponsor();
                $sponsor->setPid(129);
                $sponsor->setCalendar($calendar);
                $sponsor->setDay((int)$record['tag']);
                $sponsor->setEntity($entity);
                $sponsor->addPrize($prize);

                $sponsorRepository->add($sponsor);
                $persistenceManager->persistAll();
            } else {
                $sponsor->addPrize($prize);
                $sponsorRepository->update($sponsor);
                $persistenceManager->persistAll();
            }
            
            $processed++;
        }

        $io->success("Processed {$processed} records successfully");
        return Command::SUCCESS;
    }

    /**
     * Update calendar from CSV file
     */
    protected function updateCalendar(InputInterface $input, SymfonyStyle $io): int
    {
        $csvFile = $input->getArgument('csv-file');

        if (!$csvFile) {
            $io->error('CSV file path is required for update action');
            return Command::FAILURE;
        }

        if (!file_exists($csvFile)) {
            $io->error("CSV file not found: {$csvFile}");
            return Command::FAILURE;
        }

        $io->title('Updating calendar from CSV');

        $prizeRepository = GeneralUtility::makeInstance(PrizeRepository::class);
        $reader = Reader::createFromPath($csvFile, 'r');
        $reader->setDelimiter(';');
        $reader->setHeaderOffset(0);
        $records = $reader->getRecords();

        $updated = 0;
        foreach ($records as $offset => $record) {
            $prize = $prizeRepository->findByUid((int)$record['uid']);
            if ($prize && !empty($record['nummer'])) {
                $prize->setWinningNumber($record['nummer']);
                $prize->setName($record['gewinn']);
                $prizeRepository->update($prize);
                $updated++;
            }
        }

        $io->success("Updated {$updated} prizes successfully");
        return Command::SUCCESS;
    }

    /**
     * Import calendar data
     */
    protected function importCalendar(SymfonyStyle $io): int
    {
        $io->title('Importing calendar data');

        $calendarId = 8;
        $sponsorPid = 129;
        $calendarPid = 128;

        $calendarRepository = GeneralUtility::makeInstance(CalendarRepository::class);
        $prizeRepository = GeneralUtility::makeInstance(PrizeRepository::class);
        $sponsorRepository = GeneralUtility::makeInstance(SponsorRepository::class);
        $entityRepository = GeneralUtility::makeInstance(EntityRepository::class);

        $calendar = $calendarRepository->findByUid($calendarId);
        if (!$calendar) {
            $io->error("Calendar with ID {$calendarId} not found");
            return Command::FAILURE;
        }

        // Hide all entities on sponsor page
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_entity_domain_model_entity');
        $queryBuilder->update('tx_entity_domain_model_entity')
            ->where($queryBuilder->expr()->eq('pid', $sponsorPid))
            ->set('hidden', '1')
            ->executeStatement();

        // Get unique entities from adv table
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('adv');
        $items = $queryBuilder->select('*')
            ->from('adv')
            ->groupBy('adv.entity')
            ->executeQuery()
            ->fetchAllAssociative();

        // Unhide entities that are in use
        foreach ($items as $item) {
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('tx_entity_domain_model_entity');
            $queryBuilder->update('tx_entity_domain_model_entity')
                ->where($queryBuilder->expr()->eq('uid', $item['entity']))
                ->set('hidden', '0')
                ->executeStatement();
        }

        // Get all adv records for processing
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable('adv');
        $items = $queryBuilder->select('*')
            ->from('adv')
            ->orderBy('adv.id', 'ASC')
            ->executeQuery()
            ->fetchAllAssociative();

        $persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);
        $sponsorCache = [];
        $processed = 0;

        foreach ($items as $item) {
            $io->writeln("Processing entity: {$item['entity']}");
            $entity = $entityRepository->findByUid($item['entity']);

            // Check sponsor cache
            if (isset($sponsorCache[$item['entity']][$item['day']])) {
                $sponsor = $sponsorCache[$item['entity']][$item['day']];
            } else {
                $sponsor = $sponsorRepository->existsSponsor($calendar, $entity, $item['day'], $sponsorPid);
            }

            if ($sponsor === null) {
                $sponsor = GeneralUtility::makeInstance(Sponsor::class);
                $sponsor->setCalendar($calendar);
                $sponsor->setEntity($entity);
                $sponsor->setDay((int)$item['tag']);
                $sponsor->setPid($sponsorPid);
                $sponsorRepository->add($sponsor);
                $persistenceManager->persistAll();
            }

            $sponsorCache[$item['entity']][$item['day']] = $sponsor;

            $prize = GeneralUtility::makeInstance(Prize::class);
            $prize->setSponsor($sponsor);
            $prize->setPid($calendarPid);
            $prize->setName($item['Preise']);
            $prize->setWinningNumber('');
            $prizeRepository->add($prize);

            $persistenceManager->persistAll();
            $processed++;
        }

        $io->success("Imported {$processed} records successfully");
        return Command::SUCCESS;
    }
}