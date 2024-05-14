<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Class sync
 *
 * @package    local_campusonline_extension
 * @copyright  2024, TU Graz
 * @author     think-modular (stefan.weber@think-modular.com)
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace local_campusonline_extension;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ConnectException;

defined('MOODLE_INTERNAL') || die;

class sync {

    protected $path;
    protected $token;
    protected $error;

    /**
     * Constructor.
     */
    public function __construct() {

        // Get settings.
        $this->path = get_config('local_campusonline_extension', 'endpoint');
        $this->path = rtrim($this->path, '/');
        $clientid = get_config('local_campusonline_extension', 'clientid');
        $secret = get_config('local_campusonline_extension', 'clientsecret');

        // Make request.
        $url = $this->path . '/public/sec/auth/realms/CAMPUSonline_SP/protocol/openid-connect/token';
        $client = new Client([
            'base_uri' => $url,
            'timeout' => 10.0,
            'connect_timeout' => 2.0,
        ]);
        $response = $client->request('POST', $url, [
            'headers' => [
                'Content-Type' => 'application/x-www-form-urlencoded',
            ],
            'form_params' => [
                'grant_type' => 'client_credentials',
                'client_id' => $clientid,
                'client_secret' => $secret
            ]
        ]);

        // Analyze response.
        $response_body = $response->getBody()->getContents();
        $response_object = json_decode($response_body, false);
        $response_array = (array)$response_object;

        // Analyze response.
        if (array_key_exists('error', $response_array)) {
            $this->error = $response_array['error'] . ': ' . $response_array['error_description'];
        } elseif (array_key_exists('access_token', $response_array)) {
            $this->token = $response_array['access_token'];
        } else {
            $this->error = $response_array['error'] . ': ' . get_string('error:unknown', 'local_campusonline_extension');
        }
    }

    /**
     * Checks if connection was successful.
     */
    public function isConnected() {
        return !empty($this->token);
    }

    /**
     * Returns the error message.
     */
    public function getError() {
        return $this->error;
    }

    /**
     * Gets courses.
     */
    public function getCourses() {

        $endpoint = 'co-tm-core/course/api/courses';
        $query = [
            'semester_key' => '2022W',
            'only_elearning_courses' => 'true',
        ];

        return $this->restCall($endpoint, $query);

    }

    /**
     * Calls REST API.
     *
     * @param string $endpoint
     * @param array $query
     *
     * @return object
     */
    private function restCall($endpoint, $query) {

        // Set params.
        $url = $this->path . '/' . $endpoint;
        $client = new Client([
            'base_uri' => $url,
            'timeout' => 10.0,
            'connect_timeout' => 2.0,
        ]);

        // Make request.
        $response = $client->request('GET', $url, [
            'headers' => [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer ' . $this->token
            ],
            'query' => $query
        ]);

        // Analyze response.
        $response_body = $response->getBody()->getContents();
        $response_object = json_decode($response_body, false);
        return $response_object;
    }

}
