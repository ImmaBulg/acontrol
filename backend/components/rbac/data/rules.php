<?php

use backend\components\rbac\rules\TaskCommentDeleteOwnRule;
use backend\components\rbac\rules\ClientOwnRule;
use backend\components\rbac\rules\SiteOwnRule;
use backend\components\rbac\rules\TenantOwnRule;

return [
	// TaskComment
	(new TaskCommentDeleteOwnRule)->name => serialize((new TaskCommentDeleteOwnRule)),
	// Check client owner
	(new ClientOwnRule)->name => serialize((new ClientOwnRule)),
	// Check of site owner
	(new SiteOwnRule)->name => serialize((new SiteOwnRule)),
	// Check of tenant owner
	(new TenantOwnRule)->name => serialize((new TenantOwnRule)),
];
