<?php
/**
 * Copyright (c) Enalean, 2013 - 2018. All Rights Reserved.
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

class Tracker_Permission_PermissionChecker {
    /** @var UserManager */
    private $user_manager;

    /** @var ProjectManager */
    private $project_manager;

    public function __construct(UserManager $user_manager, ProjectManager $project_manager) {
        $this->user_manager    = $user_manager;
        $this->project_manager = $project_manager;
    }

    /**
     * Check if a user can view a given artifact
     *
     * @param PFUser $user
     * @param Tracker_Artifact $artifact
     * @return boolean
     */
    public function userCanView(PFUser $user, Tracker_Artifact $artifact) {
        if ($user->isSuperUser()) {
            return true;
        }

        if ($user->isMember($artifact->getTracker()->getGroupId(), 'A')) {
            return true;
        }

        if ($this->isTrackerAdmin($user, $artifact)) {
            return true;
        }

        $project = $artifact->getTracker()->getProject();

        if ($this->userIsRestrictedAndNotMemberOfProject($user, $project)
            || $this->userIsNotMemberOfPrivateProject($user, $project)
        ) {
            return false;
        }

        if ($this->userCanViewArtifact($user, $artifact)) {
            return $this->userHavePermissionOnTracker($user, $artifact);
        }

        return false;
    }

    private function userIsNotMemberOfPrivateProject(PFUser $user, Project $project) {
        return (! $user->isMember($project->getID()) && ! $project->isPublic());
    }

    private function userIsRestrictedAndNotMemberOfProject(PFUser $user, Project $project) {
        return ($user->isRestricted()
                && ! $user->isMember($project->getID())
                && $this->project_manager->checkRestrictedAccessForUser($project, $user)
        );
    }

    private function isTrackerAdmin(PFUser $user, Tracker_Artifact $artifact) {
        return $artifact->getTracker()->userIsAdmin($user);
    }

    private function userCanViewArtifact(PFUser $user, Tracker_Artifact $artifact) {
        if ($artifact->useArtifactPermissions()) {
            $rows = $artifact->permission_db_authorized_ugroups(Tracker_Artifact::PERMISSION_ACCESS);

            if ($rows !== false) {
                foreach ($rows as $row) {
                    if ($this->userBelongsToGroup($user, $artifact, $row['ugroup_id'])) {
                        return true;
                    }
                }
            }
            return false;
        }
        return true;
    }

    public function userCanViewTracker(PFUser $user, Tracker $tracker) {

        if ($user->isSuperUser()) {
            return true;
        }

        $project = $tracker->getProject();
        if (! $project->isActive()) {
            return false;
        }

        if ($tracker->userIsAdmin($user)) {
            return true;
        }

        if ($this->userIsRestrictedAndNotMemberOfProject($user, $project)
            || $this->userIsNotMemberOfPrivateProject($user, $project)) {
            return false;
        }

        foreach ($tracker->getPermissionsByUgroupId() as $ugroup_id => $permission_types) {
            if ($user->isMemberOfUGroup($ugroup_id, $tracker->getGroupId())) {
                return true;
            }
        }

        return false;
    }

    private function userHavePermissionOnTracker(PFUser $user, Tracker_Artifact $artifact) {
        $permissions = $artifact->getTracker()->getAuthorizedUgroupsByPermissionType();

        foreach ($permissions  as $permission_type => $ugroups) {
            switch($permission_type) {
                case Tracker::PERMISSION_FULL:
                    foreach ($ugroups as $ugroup) {
                        if ($this->userBelongsToGroup($user, $artifact, $ugroup)) {
                            return true;
                        }
                    }
                    break;

                case Tracker::PERMISSION_SUBMITTER:
                    foreach ($ugroups as $ugroup) {
                        if ($this->userBelongsToGroup($user, $artifact, $ugroup)) {
                            // check that submitter is also a member
                            $submitter = $this->user_manager->getUserById($artifact->getSubmittedBy());
                            if ($this->userBelongsToGroup($submitter, $artifact, $ugroup)) {
                                return true;
                            }
                        }
                    }
                break;

                case Tracker::PERMISSION_ASSIGNEE:
                    foreach ($ugroups as $ugroup) {
                        if ($this->userBelongsToGroup($user, $artifact, $ugroup)) {
                            // check that one of the assignees is also a member
                            $permission_assignee = new Tracker_Permission_PermissionRetrieveAssignee($this->user_manager);
                            foreach ($permission_assignee->getAssignees($artifact) as $assignee) {
                                if ($this->userBelongsToGroup($assignee, $artifact, $ugroup)) {
                                    return true;
                                }
                            }
                        }
                    }
                break;

                case Tracker::PERMISSION_SUBMITTER_ONLY:
                    foreach ($ugroups as $ugroup) {
                        if ($this->userBelongsToGroup($user, $artifact, $ugroup)) {
                            if ($user->getId() == $artifact->getSubmittedBy()) {
                                return true;
                            }
                        }
                    }
                    break;
            }
        }
        return false;
    }

    private function userBelongsToGroup(PFUser $user, Tracker_Artifact $artifact, $ugroup_id) {
        return $user->isMemberOfUGroup($ugroup_id, $artifact->getTracker()->getGroupId());
    }
}
