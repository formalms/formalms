delete from learning_middlearea where obj_index like 'tb_assessment';

delete from core_menu_under_elearning where module_name like 'preassessment';

-- remove folder appLms/admin/modules/preassessment.*.*

-- remove file appLms/controllers/AssessmentLmsController.php

-- remove file appLms/models/AssessmentLms.php

-- remove folder appLms/view/assessment/*.*