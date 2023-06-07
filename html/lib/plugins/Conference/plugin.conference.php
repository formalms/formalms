<?php

/*
 * FORMA - The E-Learning Suite
 *
 * Copyright (c) 2013-2023 (Forma)
 * https://www.formalms.org
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 *
 * from docebo 4.0.5 CE 2008-2012 (c) docebo
 * License https://www.gnu.org/licenses/old-licenses/gpl-2.0.txt
 */

interface PluginConferenceInterface
{
    /*
     * Function to add new room for make a conference.
     *
     * @param 	int 			$idConference 		reference to unique identifier in forma db (core_conference.id)
     * @param 	string 		$name 						the name of the room
     * @param 	datetime  $startDate 				the start time of conference in format (Y-m-d H:i:s)
     * @param 	datetime 	$endDate 					the end time of conference in format (Y-m-d H:i:s)
     * @param 	int       $maxParticipants 	the max number of participants
     *
     * @return array	return an array array( errorcode => int, errormessage => string, roomid => int )
     *
     */
    public function insertRoom(
          $idConference, $name, $startDate, $endDate, $maxParticipants
    );

    /*
     * Function to remove the room
     *
     * @param 	int 			$idConference 		reference to unique identifier in forma db (core_conference.id)
     *
     * @return array	return an array array( errorcode => int, errormessage => string, roomid => int )
     *
     */
    public function deleteRoom(
        $idConference
    );

    /*
     * Function for get the conference url of provider
     *
     * @param 	int 			$idConference 		reference to unique identifier in forma db (core_conference.id)
     * @param 	string 		$roomType 				(deprecated)
     *
     * @return string			url for connection to the room on the provider's server
     *
     */
    public function getUrl(
        $idConference, $roomType
    );
}

/**
 * Base class of plugin conference.
 * The plugin must implement the FormaPluginConferenceInterface interface.
 */
abstract class PluginConference implements PluginConferenceInterface
{
}

/**
 * @deprecated
 */
interface FormaPluginConferenceInterface extends PluginConferenceInterface
{
}

/**
 * @deprecated
 */
abstract class FormaPluginConference extends PluginConference
{
}
