/*-------------------------------------------------------+
| YFC Colin/CiviCRM UI Modifications                     |
| Copyright (C) 2017 SYSTOPIA                            |
| Author: B. Endres (endres -at- systopia.de)            |
| http://www.systopia.de/                                |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL license. You can redistribute it and/or     |
| modify it under the terms of this license which you    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+--------------------------------------------------------*/

// this variable will be replaced with a JSON data blob
var batches = BATCHES;

// simply append a row to the table once ready
cj(document).ready(function() {
  cj("table.crm-info-panel").first().append('<tr><td class="label">Batches</td><td>' + batches['string'] + '</td></tr>');
});
