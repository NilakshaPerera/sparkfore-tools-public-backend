<?php

namespace Database\Seeders;

use App\Domain\Models\PluginType;
use Illuminate\Database\Seeder;

class PluginTypesSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // data from https://moodledev.io/docs/4.4/apis/plugintypes
        return;
        $pluginTypes = [
            ["component_name" => "mod", "moodle_path" => "/mod"],
            ["component_name" => "antivirus", "moodle_path" => "/lib/antivirus"],
            ["component_name" => "assignsubmission", "moodle_path" => "/mod/assign/submission"],
            ["component_name" => "assignfeedback", "moodle_path" => "/mod/assign/feedback"],
            ["component_name" => "booktool", "moodle_path" => "/mod/book/tool"],
            ["component_name" => "customfield", "moodle_path" => "/customfield/field"],
            ["component_name" => "datafield", "moodle_path" => "/mod/data/field"],
            ["component_name" => "datapreset", "moodle_path" => "/mod/data/preset"],
            ["component_name" => "ltisource", "moodle_path" => "/mod/lti/source"],
            ["component_name" => "fileconverter", "moodle_path" => "/files/converter"],
            ["component_name" => "ltiservice", "moodle_path" => "/mod/lti/service"],
            ["component_name" => "mlbackend", "moodle_path" => "/lib/mlbackend"],
            ["component_name" => "forumreport", "moodle_path" => "/mod/forum/report"],
            ["component_name" => "quiz", "moodle_path" => "/mod/quiz/report"],
            ["component_name" => "quizaccess", "moodle_path" => "/mod/quiz/accessrule"],
            ["component_name" => "scormreport", "moodle_path" => "/mod/scorm/report"],
            ["component_name" => "workshopform", "moodle_path" => "/mod/workshop/form"],
            ["component_name" => "workshopallocation", "moodle_path" => "/mod/workshop/allocation"],
            ["component_name" => "workshopeval", "moodle_path" => "/mod/workshop/eval"],
            ["component_name" => "block", "moodle_path" => "/blocks"],
            ["component_name" => "qtype", "moodle_path" => "/question/type"],
            ["component_name" => "qbehaviour", "moodle_path" => "/question/behaviour"],
            ["component_name" => "qformat", "moodle_path" => "/question/format"],
            ["component_name" => "filter", "moodle_path" => "/filter"],
            ["component_name" => "editor", "moodle_path" => "/lib/editor"],
            ["component_name" => "atto", "moodle_path" => "/lib/editor/atto/plugins"],
            ["component_name" => "enrol", "moodle_path" => "/enrol"],
            ["component_name" => "auth", "moodle_path" => "/auth"],
            ["component_name" => "tool", "moodle_path" => "/admin/tool"],
            ["component_name" => "logstore", "moodle_path" => "/admin/tool/log/store"],
            ["component_name" => "availability", "moodle_path" => "/availability/condition"],
            ["component_name" => "calendartype", "moodle_path" => "/calendar/type"],
            ["component_name" => "message", "moodle_path" => "/message/output"],
            ["component_name" => "format", "moodle_path" => "/course/format"],
            ["component_name" => "dataformat", "moodle_path" => "/dataformat"],
            ["component_name" => "profilefield", "moodle_path" => "/user/profile/field"],
            ["component_name" => "report", "moodle_path" => "/report"],
            ["component_name" => "coursereport", "moodle_path" => "/course/report"],
            ["component_name" => "gradeexport", "moodle_path" => "/grade/export"],
            ["component_name" => "gradeimport", "moodle_path" => "/grade/import"],
            ["component_name" => "gradereport", "moodle_path" => "/grade/report"],
            ["component_name" => "gradingform", "moodle_path" => "/grade/grading/form"],
            ["component_name" => "mnetservice", "moodle_path" => "/mnet/service"],
            ["component_name" => "webservice", "moodle_path" => "/webservice"],
            ["component_name" => "repository", "moodle_path" => "/repository"],
            ["component_name" => "portfolio", "moodle_path" => "/portfolio"],
            ["component_name" => "search", "moodle_path" => "/search/engine"],
            ["component_name" => "media", "moodle_path" => "/media/player"],
            ["component_name" => "plagiarism", "moodle_path" => "/plagiarism"],
            ["component_name" => "cachestore", "moodle_path" => "/cache/stores"],
            ["component_name" => "cachelock", "moodle_path" => "/cache/locks"],
            ["component_name" => "theme", "moodle_path" => "/theme"],
            ["component_name" => "local", "moodle_path" => "/local"],
            ["component_name" => "contenttype", "moodle_path" => "/contentbank/contenttype"],
            ["component_name" => "h5plib", "moodle_path" => "/h5p/h5plib"],
            ["component_name" => "qbank", "moodle_path" => "/question/bank"],
            ["component_name" => "paygw", "moodle_path" => "/payment/gateway"],
            ["component_name" => "factor", "moodle_path" => "/admin/tool/mfa/factor"],
            ["component_name" => "tiny", "moodle_path" => "/lib/editor/tiny/plugins"],
        ];

        foreach ($pluginTypes as $pluginType) {
            PluginType::updateOrCreate(
                ['component_name' => $pluginType['component_name']],
                ['moodle_path' => $pluginType['moodle_path']]
            );
        }

    }
}
