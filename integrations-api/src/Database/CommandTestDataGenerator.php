<?php declare(strict_types=1);


namespace Reconmap\Database;


use Reconmap\Models\Command;
use Reconmap\Repositories\CommandRepository;

readonly class CommandTestDataGenerator
{
    public function __construct(private CommandRepository $commandRepository)
    {

    }

    public function run(): void
    {
        $command = new Command();
        $command->creator_uid = 1;
        $command->name = 'Goohost';
        $command->description = 'Extracts hosts/subdomains, IP or emails for a specific domain with Google search.';
        $command->arguments = '-t {{{Domain|||nmap.org}}}';
        $command->output_filename = null;
        $command->more_info_url = null;
        $command->tags = json_encode(['google', 'domain']);
        $command->output_parser = null;
        $this->commandRepository->insert($command);

        $command = new Command();
        $command->creator_uid = 1;
        $command->name = 'Nmap';
        $command->description = 'Scans all reserved TCP ports on the machine';
        $command->arguments = '-v {{{Host|||scanme.nmap.org}}} -oX nmap-output.xml';
        $command->output_filename = "nmap-output.xml";
        $command->more_info_url = null;
        $command->tags = json_encode(['network']);
        $command->output_parser = "nmap";
        $this->commandRepository->insert($command);

        $command = new Command();
        $command->creator_uid = 1;
        $command->name = 'Whois';
        $command->description = 'Retrieves information about domain';
        $command->arguments = '{{{Domain|||nmap.org}}}';
        $command->output_filename = null;
        $command->more_info_url = null;
        $command->tags = json_encode(['domain']);
        $command->output_parser = null;
        $this->commandRepository->insert($command);

        $command = new Command();
        $command->creator_uid = 1;
        $command->name = 'SQLmap';
        $command->description = 'Runs SQL map scan';
        $command->arguments = '-u {{{Host|||localhost}}} --method POST --data "{{{Data|||username=foo&password=bar}}}" -p username --level 5 --dbms=mysql -v 1 --tables';
        $command->output_filename = null;
        $command->more_info_url = null;
        $command->tags = json_encode(['sql', 'database']);
        $command->output_parser = 'sqlmap';
        $this->commandRepository->insert($command);
    }
}
