<?php declare(strict_types=1);

namespace BitSynama\Lapis\Modules\SystemMonitor\Controllers;

use BitSynama\Lapis\Framework\Controllers\AbstractController;
use BitSynama\Lapis\Framework\DTO\ActionResponse;
use BitSynama\Lapis\Framework\Foundation\PsrAttributeParser;
use BitSynama\Lapis\Lapis;
use Psr\Http\Message\ServerRequestInterface;
use const DIRECTORY_SEPARATOR;

final class SummaryController extends AbstractController
{
    public function psr(ServerRequestInterface $request): ActionResponse
    {
        /** @var string $repoDir */
        $repoDir = Lapis::varRegistry()->get('repo_dir');
        $repoDir .= DIRECTORY_SEPARATOR . 'src';
        $parser = new PsrAttributeParser($repoDir);
        $collection = $parser->parse();
        $groupByPsr = $collection->groupByPsr();

        return new ActionResponse(
            status: ActionResponse::SUCCESS,
            data: [
                'groupByPsr' => $groupByPsr,
            ],
            message: 'PSR Attribute Scan Summary',
            template: 'system_monitor.summary.psr'
        );
    }
}
