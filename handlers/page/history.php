<?php
/**
 * Display the history of a page.
 * 
 * @package		Handlers
 * @subpackage	Page
 * @version		$Id$
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License
 * @filesource
 * 
 * @uses		Wakka::HasAccess()
 * @uses		Wakka::LoadRevisions()
 * @uses		Wakka::LoadPageById()
 * @uses		Wakka::Format()
 * @todo		move <div> to template;
 */
/**
 * i18n
 */
 define('DIFF_ADDITIONS', 'Additions:');
 define('DIFF_DELETIONS', 'Deletions:');
 define('DIFF_NO_DIFFERENCES', 'No differences.');
 define('EDITED_ON', 'Edited on %1$s by %2$s');
 define('ERROR_ACL_READ', 'You aren\'t allowed to read this page.');
 define('HISTORY_PAGE_VIEW', 'Page view:');
 define('OLDEST_VERSION_EDITED_ON_BY', 'Oldest known version of this page was edited on %1$s by %2$s');
 define('MOST_RECENT_EDIT', 'Most recent edit on %1$s by %2$s');
 
echo '<div class="page">'."\n"; //TODO: move to templating class
if ($this->HasAccess("read")) {
	// load revisions for this page
	if ($pages = $this->LoadRevisions($this->tag))
	{
		$output = "";
		$c = 0;
		foreach ($pages as $page)
		{
			$c++;
			$pageB = $this->LoadPageById($page["id"]);
			$bodyB = explode("\n", $pageB["body"]);

			if (isset($pageA))
			{
				// show by default. We'll do security checks next.
				$allowed = 1;

				// check if the loaded pages are actually revisions of the current page! (fixes #0000046)
				if (($pageA['tag'] != $this->page['tag']) || ($pageB['tag'] != $this->page['tag'])) 
				{
					$allowed = 0;
				}
				// show if we're still allowed to see the diff
				if ($allowed) 
				{

					// prepare bodies
					$bodyA = explode("\n", $pageA["body"]);

					$added = array_diff($bodyA, $bodyB);
					$deleted = array_diff($bodyB, $bodyA);

					if (strlen($pageA['note']) == 0) $note = ''; else $note = '['.$this->htmlspecialchars_ent($pageA['note']).']';

					if ($c == 2) 
					{
						$output .= '<strong>'.sprintf(MOST_RECENT_EDIT, '<a href="'.$this->Href('', '', 'time='.urlencode($pageA['time'])).'">'.$pageA['time'].'</a>', $EditedByUser).'</strong> <span class="pagenote smaller">'.$note."</span><br />\n";
					}
					else 
					{
						$output .= '<strong>'.sprintf(EDITED_ON,        '<a href="'.$this->Href('', '', 'time='.urlencode($pageA['time'])).'">'.$pageA['time'].'</a>', $EditedByUser).'</strong> <span class="pagenote smaller">'.$note."</span><br />\n";
					}

					if ($added)
					{
						// remove blank lines
						$output .= "<br />\n<strong>".DIFF_ADDITIONS."</strong><br />\n";
						$output .= "<ins>".$this->Format(implode("\n", $added))."</ins><br />";
					}

					if ($deleted)
					{
						$output .= "<br />\n<strong>".DIFF_DELETIONS."</strong><br />\n";
						$output .= "<del>".$this->Format(implode("\n", $deleted))."</del><br />";
					}

					if (!$added && !$deleted)
					{
						$output .= "<br />\n".DIFF_NO_DIFFERENCES;
					}
					$output .= "<br />\n<hr /><br />\n";
				}
			}
			$pageA = $this->LoadPageById($page["id"]);
			$EditedByUser = $this->Format($page["user"]);
		}
		$output .= '<strong>'.sprintf(OLDEST_VERSION_EDITED_ON_BY, '<a href="'.$this->href('', '', 'time='.urlencode($pageB['time'])).'">'.$pageB['time'].'</a>', $EditedByUser).'</strong> <span class="pagenote smaller">['.$this->htmlspecialchars_ent($page['note'])."]</span></strong><br />\n";
		$output .= '<div class="revisioninfo">'.HISTORY_PAGE_VIEW.'</div>'.$this->Format(implode("\n", $bodyB));
		print($output);
	}
} else {
	print('<em class="error">'.ERROR_ACL_READ.'</em>');
}
echo '</div>'."\n" //TODO: move to templating class
?>
