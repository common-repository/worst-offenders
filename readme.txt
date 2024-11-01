=== Worst Offenders ===
Version: 3.0.alpha2
Stable tag: 3.0.alpha2
Contributors: ear1grey
Tags: comments, spam
Requires at least: 2.5
Tested up to: 2.5

Worst Offenders works cooperatively with other anti-spam plugins: its primary purpose is identifying and deleting the comments that are 100% definitely spam so that any false-positives can be easily found.

== Description ==

NOTE: Worst Offenders for Wordpress 2.5 is currently a pre-alpha release for developer feedback only.

If you use any anti-spam tool you will no doubt be delighted that spam can be automatically identified, but less happy that you now have to either trawl through the spam to check for false positives, or just give up and hope for the best - this is where Worst Offenders can help: it analyses messages already marked as spam and uses several techniques to identify messages with common sources, subjects and content - those which are definitely spam.  These _Worst Offenders_ can be deleted en-masse, hopefully leaving just a handful of messages that can be checked by hand.

== Installation ==

1. Upload the wo3 Folder to to the `/wp-content/plugins/` directory
1. Activate the plugin through the 'Plugins' menu in WordPress
1. Visit the "WorstOffenders Config" page under the Plugins tab and click "Update Options" to generate indexes for faster analysis.
1. Check out the WorstOffenders page under the main Comments tab.

== Frequently Asked Questions ==

= How are the worst offenders identified =

A series of litmus tests has been defined, each test looks for different clues that may (when seen together) make it obvious that a message really is spam.  for example, the IP test looks for multiple messages that come from the same IP address.

= What litmus tests are there? =
1. IP Address - spots multiple messages from one host
1. MD5 - spots multiple messages where the body text has the same MD5 hash
1. Link Count - finds messages that contain more than n Links.
1. Domain - identifies messages advertising the same URL
1. Email - highlights multiple messages from one email address.
1. Name length - flags messages where the author name contains more than n words
1. Obvious Name - separates messages where the author name contains words like poker, loan or tramadol.

= Can more litmus tests be devised? =
Yes, the test system is plugabble, and one day it will automatically discover new tests.

= My PHP and SQL foo is strong, how do I write a new litmus test? =
1. Copy an existing test and call it whatever you like (e.g. ExampleLitmus.php)
1. In ExampleLitmus.php change any references to the original class name to ExampleLitmus).
1. Invent your magic spam-spotting SQL and insert it the getMatches method, in order to return the comment id you must return either the field comment_id containing a single comment id, or the field comment_id_list which must contain a comma separated list of comments.
1. Modify the content method (this is what gets displayed in the tab)
1. If necessary, include an addIndexes method.
1. Include your litmus test from plugin.php.
1. Re-check these instructions, 'cause they're bound to change (and hopefully get easier) as WorstOffenders matures.

= Can it work with "XYZ" (my favourite spam catching software)? =
Yes, Worst Offenders is compatible with any spam capture system that marks comments as spam.

== Screenshots ==
1. Worst Offenders adds a tab to the Comments page showing how many of the currently queued spam comments it can delete.  Here, Akismet has discovered 11 spam comments and Worst Offenders can delete 10 of them.
1. Each separate spam litmus test provides a tab showing which messages have been identified using that test.  Of 13 spam comments detected by Akismet, WorstOffenders can remove 12.  Of these twelve, 6 were identified because they came from a common IP address, 10 contain more than five links.  Note that there is an overlap in what the two tests discover, so the total number of deletions is 12, not 16 (i.e. the total is not 6+10). 

== Tasks ==
= To Do =
You're welcome to <a href="http://boakes.org/worst-offenders-plugin">suggest things</a>!
1. Improve refresh/counter mechanism.
1. Further simplify the writing of litmus tests
1. Enable auto-discovery of litmus tests (possibly make each litmus test a separate plugin - this would use an existing mechanism which is good, but would clutter up the plugin UI, which would be bad.  perhaps WP could/should allow plugin groups / namespaces (!))
1. Sort out index creation and ensure all indexes can be properly created and dropped.
1. Improve the admin interface to allow index management.
1. Create a widget, so spammers know not to bother.
1. Provide better feedback about things that got deleted.
1. Include a warning if indexes are missing (with a create link)

= Done =
1. Highlight obvious words that are found in the obvious words litmus test.
1. Include hook to request that an IP address be banned from further posting.
1. Switch to using wp_cache wherever options were used
1. Improve count function to remove duplicates
1. Fix incorrect percentage report
1. Removed some of the debug echo's that were affecting akismet
