<?php 
//StackOverFlow problem link: https://stackoverflow.com/questions/79516395/how-to-implement-pagination-with-api-using-php/79516696#79516696


//WordPress Shortcode for getting clinical study data fetch;

add_shortcode('external_data', 'fetch_clinical_trials');

function call_clinic_api(){
    $nextPageToken = isset($_GET['pageToken']) ? $_GET['pageToken'] : '';

    if(!empty($nextPageToken)){
        $response= wp_remote_get("https://clinicaltrials.gov/api/v2/studies?pageToken={$nextPageToken}");
    }else{
        $response= wp_remote_get("https://clinicaltrials.gov/api/v2/studies");
    }

    if (is_wp_error($response)) {
        return 'Error fetching data.';
    }

 $result = json_decode(wp_remote_retrieve_body($response), true);
   return $result;
}

function fetch_clinical_trials($atts) {
    if (is_admin()) {
        return '<p>Shortcode [external_data] preview.</p>';
    }

    $atts = shortcode_atts(['title' => 'Clinical Trials Data'], $atts, 'external_data');
    
    $response = call_clinic_api();
    
    $studies = $response['studies'] ?? [];
    $nextPageTokenData = $response['nextPageToken'] ?? '';
    

   
    


    $html = '<h2>' . esc_html($atts['title']) . '</h2>';
    $html .= '<table class="table-auto">
                <thead>
                <tr><th class="border px-3">NCT ID</th><th class="border px-3">Organization</th><th class="border px-3">Title</th><th class="border px-3">Status</th><th class="border px-3">Start Date</th><th class="border px-3">Completion Date</th><th class="border px-3">Sponsor</th></tr> </thead>' ;
                

    foreach ($studies as $study) {
        $html .= '<tboday>';
        $html .= '<tr>';
        $html .= '<td class="border px-3">' . esc_html($study['protocolSection']['identificationModule']['nctId'] ?? 'N/A') . '</td>';
        $html .= '<td class="border px-3">' . esc_html($study['protocolSection']['identificationModule']['organization']['fullName'] ?? 'N/A') . '</td>';
        $html .= '<td class="border px-3">' . esc_html($study['protocolSection']['identificationModule']['briefTitle'] ?? 'N/A') . '</td>';
        $html .= '<td class="border px-3">' . esc_html($study['protocolSection']['statusModule']['overallStatus'] ?? 'N/A') . '</td>';
        $html .= '<td class="border px-3">' . esc_html($study['protocolSection']['statusModule']['startDateStruct']['date'] ?? 'N/A') . '</td>';
        $html .= '<td class="border px-3">' . esc_html($study['protocolSection']['statusModule']['primaryCompletionDateStruct']['date'] ?? 'N/A') . '</td>';
        $html .= '<td class="border px-3">' . esc_html($study['protocolSection']['sponsorCollaboratorsModule']['leadSponsor']['name'] ?? 'N/A') . '</td>';
        $html .= '</tr>';
        $html .= '</tbody>';
    };

    
    $html .= '</table>';
    $html.= '<div class="flex justify-between w-full border">';
    $html.= "<a href='' class='inline-block w-full'>Previous Page</a>";
    $html.= "<a class='ml-5 inline-block w-full text-right' href='?pageToken=" . urlencode($nextPageTokenData) . "'>Next Page</a>";
    $html.= '<div>';
    
    return $html;
}