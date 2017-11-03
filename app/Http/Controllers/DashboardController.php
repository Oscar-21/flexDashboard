<?php
namespace App\Http\Controllers;

use Response;
use Auth;
use JWTAuth;

// Service Classes 
use App\Services\AppearanceService;
use App\Services\JoinsService;
use App\Services\RMarkdownService;

// Eloquent Models
use App\User;
use App\Appearance;

class DashBoardController extends Controller {

    // Inherited Classes
    protected $appearanceService;
    protected $joinsService;
    protected $rmarkdownService;


    /**
     * Constructor
     */
    public function __construct(
        AppearanceService $appearanceService, 
        JoinsService $joinsService,
        RMarkdownService $rmarkdownService 
    ) 
    {

        // Inherited Classes
        $this->appearanceService = $appearanceService;
        $this->joinsService = $joinsService;
        $this->rmarkdownService = $rmarkdownService;

    }

    /**
     * JOINS
     */
    public function Joins($spaceId) {
        $memberSignUpData = $this->joinsService->spaceUserJoins($spaceId);
        $n = count($memberSignUpData);
        $sortedMemberData = array_slice($memberSignUpData, 0, ($n - 5));
        $firstYear = $memberSignUpData[$n - 4]; 
        $lastYear = $memberSignUpData[$n - 3];
        $firstMonth = $memberSignUpData[$n - 2];
        $lastMonth = $memberSignUpData[$n - 1];

        $this->rmarkdownService->generateMemberJoinsRmd(
          $firstYear, 
          $lastYear, 
          $firstMonth, 
          $lastMonth, 
          $sortedMemberData
        );
    }

    /**
     * Generate Appearances visualizations using RMarkdown 
     * @param $spaceId
     * @return Illuminate\Support\Facades\Response::class
     */
    public function Appearances($spaceId) {

        // Write head of RMarkdown File
        $this->rmarkdownService->generateTitle("Tim");

        // Get appearances from database by occasion
        $appearances = array(
            'all' => $this->appearanceService->getAllAppearances($spaceId), 
            'event' => $this->appearanceService->getEventAppearances($spaceId),
            'work' => $this->appearanceService->getNonEventAppearances($spaceId, 'work'),
            'booking' => $this->appearanceService->getNonEventAppearances($spaceId, 'booking'),
            'student' => $this->appearanceService->getNonEventAppearances($spaceId, 'student'),
            'invite' => $this->appearanceService->getNonEventAppearances($spaceId, 'invite')
        );


        // Create a seperate dataset for each occasion
        foreach ($appearances as $key => $appearance) {
            $n = count($appearance);
            $sortedAppearances = array_slice($appearance, 0, ($n - 5));
            $firstYear = $appearance[$n - 4]; 
            $lastYear = $appearance[$n - 3];
            $firstMonth = $appearance[$n - 2];
            $lastMonth = $appearance[$n - 1];
            
            // Insert data into RMarkdown Script
            $this->rmarkdownService->generateMemberAppearancesRmd(
                $firstYear, 
                $lastYear, 
                $firstMonth, 
                $lastMonth, 
                $sortedAppearances, 
                $key,
                (count($appearances) - 1) // for keeping track of function calls
            );
        }
    }

    public function inviteHelper() {

    }
}
