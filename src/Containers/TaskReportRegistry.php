<?php

/**
 * This is part of the FiberLoop package.
 *
 * @package    FiberLoop
 * @category   Container
 * @license    https://opensource.org/license/mit/  MIT License
 * @copyright  Copyright (c) 2023, Vidda
 * @author     Vidda <vidda@ascetik.fr>
 */

declare(strict_types=1);

namespace Ascetik\Fiberloop\Containers;

use Ascetik\Fiberloop\ValueObjects\TaskReport;
use Ascetik\Storage\Box;

/**
 * Register task reports
 * @version 1.0.0
 */
final class TaskReportRegistry extends Box
{
    public function push(TaskReport $report)
    {
        $this->attach($report);
    }
}
