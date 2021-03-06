<?php
/**
 * Copyright (c) Enalean, 2020 - present. All Rights Reserved.
 *
 * This file is a part of Tuleap.
 *
 * Tuleap is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * Tuleap is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Tuleap. If not, see <http://www.gnu.org/licenses/>.
 */

declare(strict_types = 1);

namespace Tuleap\Tracker\Creation;

use HTTPRequest;
use Project;
use Tuleap\Layout\BaseLayout;
use Tuleap\Request\DispatchableWithBurningParrot;
use Tuleap\Request\DispatchableWithProject;
use Tuleap\Request\DispatchableWithRequest;
use Tuleap\Request\ForbiddenException;
use Tuleap\Request\NotFoundException;
use Tuleap\Tracker\TrackerIsInvalidException;

class TrackerCreationProcessorController implements DispatchableWithRequest, DispatchableWithProject, DispatchableWithBurningParrot
{
    /**
     * @var \UserManager
     */
    private $user_manager;

    /**
     * @var \ProjectManager
     */
    private $project_manager;

    /**
     * @var TrackerCreator
     */
    private $tracker_creator;

    /**
     * @var TrackerCreationPermissionChecker
     */
    private $permission_checker;

    public function __construct(
        \UserManager $user_manager,
        \ProjectManager $project_manager,
        TrackerCreator $tracker_creator,
        TrackerCreationPermissionChecker $permission_checker
    ) {
        $this->user_manager       = $user_manager;
        $this->project_manager    = $project_manager;
        $this->tracker_creator    = $tracker_creator;
        $this->permission_checker = $permission_checker;
    }

    /**
     * Is able to process a request routed by FrontRouter
     *
     * @param array $variables
     *
     * @throws NotFoundException
     * @throws ForbiddenException
     */
    public function process(HTTPRequest $request, BaseLayout $layout, array $variables): void
    {
        $project = $this->getProject($variables);
        $user    = $this->user_manager->getCurrentUser();

        $csrf = new \CSRFSynchronizerToken(
            $this->getRouteToSelf($project)
        );

        $csrf->check();

        $this->permission_checker->checkANewTrackerCanBeCreated($project, $user);

        $tracker_name = $request->get('tracker-name');
        $tracker_shortname = $request->get('tracker-shortname');
        $tracker_description = $request->get('tracker-description') ?? '';
        $tracker_template_id = $request->get('tracker-template-id');

        try {
            $tracker = $this->tracker_creator->duplicateTracker(
                $project,
                (string)$tracker_name,
                (string)$tracker_description,
                (string)$tracker_shortname,
                (string)$tracker_template_id
            );

            $this->redirectToTrackerAdmin($tracker);
        } catch (TrackerCreationHasFailedException $exception) {
             $this->redirectToTrackerCreation(
                 $project,
                 dgettext('tuleap-tracker', 'An error occured while creating the tracker.')
             );
        } catch (TrackerIsInvalidException $exception) {
            $this->redirectToTrackerCreation(
                $project,
                $exception->getTranslatedMessage()
            );
        }
    }

    /**
     * Return the project that corresponds to current URI
     *
     * This part of controller is needed when you implement a new route without providing a $group_id.
     * It's the preferred way to deal with those kind of URLs over Event::GET_PROJECTID_FROM_URL
     *
     * @param array $variables
     */
    public function getProject(array $variables) : Project
    {
        return $this->project_manager->getValidProjectByShortNameOrId($variables['project_name']);
    }

    private function redirectToTrackerCreation(Project $project, string $reason): void
    {
        $GLOBALS['Response']->addFeedback(\Feedback::ERROR, $reason);
        $GLOBALS['Response']->redirect('/' . urlencode($project->getUnixNameLowerCase()) . '/tracker/new');
    }

    private function redirectToTrackerAdmin(\Tracker $tracker): void
    {
        $GLOBALS['Response']->redirect($tracker->getAdministrationUrl());
    }

    private function getRouteToSelf(Project $project) : string
    {
        return '/' . urlencode($project->getUnixNameLowerCase()) . '/tracker/new-information';
    }
}
