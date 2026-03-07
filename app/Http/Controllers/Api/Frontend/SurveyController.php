<?php

namespace App\Http\Controllers\Api\Frontend;

use App\Http\Controllers\Controller;
use App\Helpers\Helper;
use App\Models\Survey;
use App\Models\SurveyVote;
use App\Models\SurveyOpinion;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;

class SurveyController extends Controller
{
    use ApiResponse;

    public function index()
    {
        // Get the current date to filter out surveys that have ended
        $currentDate = now();

        // Retrieve active surveys with their opinions
        $surveys = Survey::with(['opinions' => function ($query) {
            $query->withCount('votes as vote_count');
        }])
            ->select('id', 'title', 'description', 'end_date')
            ->where('status', 'active')
            ->where('end_date', '>=', $currentDate)
            ->get();

        // Transform surveys to include results like the show method
        $surveys->transform(function ($survey) {
            // Add stripped description for Flutter
            $survey->description_for_flutter = strip_tags($survey->description);

            // Calculate total votes for this survey
            $totalVotes = SurveyVote::where('survey_id', $survey->id)->count();

            // Transform opinions to include vote counts and percentages
            $results = $survey->opinions->map(function ($opinion) use ($totalVotes) {
                $percentage = $totalVotes > 0 ? round(($opinion->vote_count / $totalVotes) * 100, 2) : 0;
                return [
                    'id'                => $opinion->id,
                    'opinion_text'      => $opinion->opinion,
                    'votes'             => $opinion->vote_count,
                    'percentage'        => $percentage,
                ];
            });

            // Add results and total votes to survey
            $survey->results = $results;
            $survey->total_votes = $totalVotes;

            // Remove the original opinions relation to clean up response
            unset($survey->opinions);

            return $survey;
        });

        $data = [
            'surveys' => $surveys,
        ];

        return Helper::jsonResponse(true, 'Get all active surveys with results', 200, $data);
    }

    public function show($id)
    {
        $survey = Survey::with(['opinions' => function ($query) {
            $query->withCount('votes as vote_count');
        }])->findOrFail($id);

        // Add stripped description for Flutter
        $survey->description_for_flutter = strip_tags($survey->description);

        // Calculate total votes for this survey
        $totalVotes = SurveyVote::where('survey_id', $survey->id)->count();

        // Transform opinions to include vote counts and percentages
        $results = $survey->opinions->map(function ($opinion) use ($totalVotes) {
            $percentage = $totalVotes > 0 ? round(($opinion->vote_count / $totalVotes) * 100, 2) : 0;
            return [
                'id'                => $opinion->id,
                'opinion_text'      => $opinion->opinion,
                'votes'             => $opinion->vote_count,
                'percentage'        => $percentage,
            ];
        });

        // Add results and total votes to survey
        $survey->results = $results;
        $survey->total_votes = $totalVotes;

        // Remove the original opinions relation to clean up response
        unset($survey->opinions);

        return Helper::jsonResponse(true, 'Get a single survey with results', 200, $survey);
    }

    public function vote(Request $request)
    {
        $request->validate([
            'survey_id'     => 'required|exists:surveys,id',
            'opinion_id'    => 'required|exists:survey_opinions,id',
        ]);

        $user = auth("api")->user();
        $survey = Survey::findOrFail($request->survey_id);

        $vote_exists = SurveyVote::where('survey_id', $survey->id)->where('user_id', $user->id)->exists();
        if ($vote_exists) {
            return Helper::jsonErrorResponse('You have already voted for this survey.', 409);
        }

        // Store single vote
        SurveyVote::create([
            'survey_id'         => $survey->id,
            'survey_opinion_id' => $request->opinion_id,
            'user_id'           => $user->id,
        ]);

        return Helper::jsonResponse(true, 'Vote submitted successfully.', 200);
    }
}
