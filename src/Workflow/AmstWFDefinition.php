<?php

namespace App\Workflow;

use App\Entity\Amst;
use Survos\StateBundle\Attribute\Place;
use Survos\StateBundle\Attribute\Transition;

#[\Survos\StateBundle\Attribute\Workflow(supports: [Amst::class], name: self::WORKFLOW_NAME)]
class AmstWFDefinition
{
	public const WORKFLOW_NAME = 'AmstWorkflow';

	#[Place(initial: true)]
	public const PLACE_NEW = 'new';

	#[Place]
	public const PLACE_DONE = 'done';

	#[Transition(from: [self::PLACE_NEW], to: self::PLACE_DONE)]
	public const TRANSITION_DO = 'do';
}
