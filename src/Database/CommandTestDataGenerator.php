<?php declare(strict_types=1);


namespace Reconmap\Database;


use Reconmap\Models\Command;
use Reconmap\Repositories\CommandRepository;

class CommandTestDataGenerator
{
    public function __construct(private readonly CommandRepository $commandRepository)
    {

    }

    public function run(): void
    {
        $command = new Command();
        $command->creator_uid = 1;
        $command->name = 'Goohost';
        $command->description = 'Extracts hosts/subdomains, IP or emails for a specific domain with Google search.';
        $command->docker_image = 'reconmap/pentest-container-tools-goohost';
        $command->arguments = '-t {{{Domain|||nmap.org}}}';
        $command->executable_type = 'rmap';
        $command->output_filename = null;
        $command->more_info_url = null;
        $command->tags = json_encode(['google', 'domain']);
        $command->output_parser = null;
        $this->commandRepository->insert($command);

        $command = new Command();
        $command->creator_uid = 1;
        $command->name = 'Nmap';
        $command->description = 'Scans all reserved TCP ports on the machine';
        $command->docker_image = 'instrumentisto/nmap';
        $command->arguments = '-v {{{Host|||scanme.nmap.org}}} -oX nmap-output.xml';
        $command->executable_type = 'rmap';
        $command->output_filename = "nmap-output.xml";
        $command->more_info_url = null;
        $command->tags = json_encode(['network']);
        $command->output_parser = "nmap";
        $this->commandRepository->insert($command);

        $command = new Command();
        $command->creator_uid = 1;
        $command->name = 'Whois';
        $command->description = 'Retrieves information about domain';
        $command->docker_image = 'zeitgeist/docker-whois';
        $command->arguments = '{{{Domain|||nmap.org}}}';
        $command->executable_type = 'rmap';
        $command->output_filename = null;
        $command->more_info_url = null;
        $command->tags = json_encode(['domain']);
        $command->output_parser = null;
        $this->commandRepository->insert($command);

        $command = new Command();
        $command->creator_uid = 1;
        $command->name = 'SQLmap';
        $command->description = 'Runs SQL map scan';
        $command->docker_image = 'paoloo/sqlmap';
        $command->arguments = '-u {{{Host|||localhost}}} --method POST --data "{{{Data|||username=foo&password=bar}}}" -p username --level 5 --dbms=mysql -v 1 --tables';
        $command->executable_type = 'rmap';
        $command->output_filename = null;
        $command->more_info_url = null;
        $command->tags = json_encode(['sql', 'database']);
        $command->output_parser = 'sqlmap';
        $this->commandRepository->insert($command);
    }
}
