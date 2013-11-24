# Improvements #

As it stands Exposure doesn't implement a full-fledged service (if only for the caveats listed in the [README](README.md) file). Here is a list of items that have not been included (or at least not fully).

**Note** - Hints are given should you want to develop them as an exercise.

## New features ##

More or less in order of increasing complexity:

- **Persist visibility of organisations by project owners**

	The name of organisations can only be seen by project owners that 1) have an active subscription, and 2) have a project that is wanted by an organisation. Which means that once a user's subscription expire, s/he can no longer see the organisation. One way to do this is to schedule a daily script (e.g. using cron) to call `addVisibleSponsorOrganisation()` (from `Exposure\Model\User`) on organisations that want subscribed user's projects but aren't already listed as visible.

- **Subscription expiration management**

	Schedule a script to expire active subscriptions, using `expireCurrentSubscription()` (from `Exposure\Model\User`).

- **Support of multiple users per project and organisation**

	The model (`Exposure\Model\User`, `Exposure\Model\Project` and `Exposure\Model\SponsorOrganisation`) already has provisions for this. Existing views and controllers need to be updated.

- **Comments**
 
 	The model is in place (`Exposure\Model\Comment*`), the views and controllers are missing.

- **Integration with a payment platform**

	Insert/call the payment workflow in `Exposure\Controller\SubscriptionPostActions`'s `subscriptionPay()` method. 
	Two URLs (`/subscription-order-confirmed` and `/subscription-order-failed`) are included to account for a payment going through or failing. 

- **Enabling users to suggest project themes**

	This feature should include moderation by an administrator and the corresponding notifications (`Exposure\Model\ProjectThemeSuggestionNotification`).

- **Internationalisation (i18n)**

	The model includes multi-language strings (`Sociable\Model\MultiLanguageString`) as a starting point for user data translation.

	Static content could for instance be translated using [Zend\I18n](http://framework.zend.com/manual/2.2/en/modules/zend.i18n.translating.html) and `.po` gettext catalogue files (with help from [poedit](http://www.poedit.net/)).  

- **Localisation (l14n)**

	Multi-currency values (`Sociable\Model\MultiCurrencyValue`) are available out-of-the-box, but localised dates, decimal separators etc. are not.

- **Keeping users signed in**

	One way to do this is to introduce session cookies and to extend the model to store these cookies (along with a reference to the user) in the database. When a user later accesses the site, check if the session cookie exists in the database, and automatically sign the matching user in. 

- **Opauth for authentication**

	[Opauth](http://opauth.org/) is a PHP library that enables users to sign in using credentials provided by various authentication providers, e.g. Google, Facebook.

	Have a look at `Sociable\Model\OpauthAuthenticator` to get started.

- **Generation of user invoices**

	[DocRaptor](https://docraptor.com/) might be worth a look.

- **Implementation of periodic tasks listed in the user's preferences, such as producing and sending weekly reports, sending the newsletter**

	Implementing this improvement goes beyond PHP programming as it will require using a scheduling tool and integrating the app with external software or services (e.g. to send newsletters). 

## Making maintenance easier ##

Overall a complete code review would be necessary to refactor the code (and track and correct bugs). Other than that, here are a few ideas that would make maintaining the code easier:

- Updating the controllers and templates so that only labels are sent by the controllers in `$_SESSION['errors']` and `$_SESSION['message']`, rather than actual content, which really shouldn't be in the code. It would then be up to the view/template to convert the label back to the appropriate content.

	For instance, the controller would set `$_SESSION['errors']['password']` to `signup.error.passwordTooShort`, and the view would display this as `password is too short` (or, for an added challenge, the local translation if translations have been implemented as suggested above).
 
- Resource-based routing, replacing literal filenames and URL paths (which can be messy to maintain and update in templates) with helper names.

- Tests for controllers and views.

## Improving user experience ##

The front-end has a minimal [Bootstrap](http://getbootstrap.com/)-based interface, which could do with a makeover. User experience could also be improved, for instance by:

- Removing Bootstrap's `has-error` class from form items marked as erroneous as soon as their content is changed by the user. 

- Validating form data asynchronously, e.g. on the sign-up page, check if the email address is available as soon as the email field loses focus with an AJAX GET request (rather than when the form is submitted).

- Dispatching email messages to a "non-blocking" Sendmail queue instead of sending them directly by SMTP, which can make the front-end freeze for a few seconds while the email is being sent. 

	Look at [Swift Mailer](http://swiftmailer.org/)'s `Swift_SendmailTransport` transport method to get started.

	Better yet, publish the email messages in the database and have a scheduler pick up and dispatch the messages separately from the main app.

	Yet another alternative would be to use an Email-as-a-Service provider to send email messages.

- Rethinking the interface and overall workflow completely, perhaps using A/B testing to improve it iteratively.

	You may want to look into [phpA/B](http://phpabtest.com/), an A/B testing framework for PHP.