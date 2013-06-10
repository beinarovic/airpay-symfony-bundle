Airpay Management Bundle
=====================

A bundle for Symfony2.2, that manages AirPay payments.

1) Installation
---------------------

AirpayBundle can be installed via Composer.
You can find this bundle on packagist: https://packagist.org/packages/beinarovic/airpay-symfony-bundle

<pre>
<code>
// composer.json
{
    // ...
    require: {
        // ..
        "beinarovic/airpay-symfony-bundle": "dev-master"

    }
}
</code>
</pre>

Then, you can install the new dependencies by running Composer's update command from the directory where your composer.json file is located:

<pre>
<code>
    php composer.phar update
</code>
</pre>

You have to add this bundle to `AppKernel.php` register bundles method, so that Symfony can use it.
<pre>
// in AppKernel::registerBundles()
$bundles = array(
    // ...
    new Beinarovic\AirpayBundle\BeinarovicAirpayBundle(),
);
</pre>

In your `config.yml` you must configure this bundle. 

<pre>
beinarovic_airpay:
    merchant_id:        12345
    merchant_secret:    MySecretKey
</pre>

`merchant_id` and `merchant_secret` are mandatory fields, they must match thoes received from the Airpay service. 

There are more available option fields:

* `is_sandbox` is `false` by default. For development this field should be changed to `true`. 
* `enable_logs` is `false` by default. If enabled this bundle will write logs to database.
* `url` is `https://www.airpayment.net/new/gateway/` by default. Airpay gateway URL.
* `sandbox_url` is `https://www.airpayment.net/sandbox/gateway/` by default. The airpay gateway URL got sandbox.

Finaly you have to update your database schema.

<pre>
php app/console doctrine:schema:update --force
</pre>

2) Usage
----------------------------------

This bundle is used as a service, you can retrieve it from the container. This service is the `AirpayManager`, that does all the magic.

<pre>
$airpaymanager = $this->get('beinarovic_airpay_manager');
</pre>

## The Form
This bundle uses Symfony forms. You must create the payment entity, this is realized dy the `AirpayManager`, configure it and render a form for it.

<code>
    $airpaymanager = $this->get('beinarovic_airpay_manager');
    
    $payment = $airpaymanager->createPayment();
    
    /**
    * Your custom field (user's id or whatever you need). This field is not posted to the gateway, 
    * it is stored and retrieved from the database.
    **/
    $payment->setCustom($userid); 
    
    /**
    * Here you can pre set form variables.
    **/
    $payment->setAmount($package);
    $payment->setCurrency('EUR');
    $payment->setClEmail('edmund@beinarovic.lv');
    $payment->setClCity('city');
    $payment->setClFname('firstname');
    $payment->setClLname('lastname');
    $payment->setClCountry('CC');
    $payment->setDescription('test transaction');
    $payment->setLanguage('ENG');
    
    /**
    * `createForm` returns a `FormInterface` object witch has to be created and displayed.
    **/
    $form = $airpaymanager->createForm($payment);
    
    return array(
        'airpayForm'    => $form->createView(),
        'action'        => $airpaymanager->getFormAction() // This retrieves the action URL.
    );
</code>

In the template you should set a custom form rendering theme. Tou shold add these blocks to your view:

<pre>    
<code>
    {# Use this to overwrite form field settings. #}
    {% form_theme airpayForm _self %}
    {% block field_widget %}
        {% include 'BeinarovicAirpayBundle:Form:field_widget.html.twig' %}
    {% endblock field_widget %}
    {% block form_widget %}
        {% include 'BeinarovicAirpayBundle:Form:field_widget.html.twig' %}
    {% endblock form_widget %}
</code>
</pre>
    
Example of form rendering in the view.
    
<pre>
<code>
        &lt;form method="post" name="pform" action="{{ action }}"&gt;
            {{ form_widget(airpayForm._cmd, { 'type': 'hidden' }) }}
            {{ form_widget(airpayForm.merchant_id, { 'type': 'hidden' }) }}
            {{ form_widget(airpayForm.amount, { 'type': 'hidden' }) }}
            {{ form_widget(airpayForm.currency, { 'type': 'hidden' }) }}
            {{ form_widget(airpayForm.invoice, { 'type': 'hidden' }) }}
            {{ form_widget(airpayForm.language, { 'type': 'hidden' }) }}
            {{ form_widget(airpayForm.cl_fname) }}
            {{ form_widget(airpayForm.cl_lname) }}
            {{ form_widget(airpayForm.cl_email) }}
            {{ form_widget(airpayForm.cl_country) }}
            {{ form_widget(airpayForm.cl_city) }}
            {{ form_widget(airpayForm.description, { 'type': 'hidden' }) }}
            &lt;input type="submit" value="Pay"&gt;
        &lt;/form&gt;
</code>
</pre>

## User Redirect Action

At the user redirect action, you can validate and retrieve the payment. In this action, you shold not store the payment, just show the result to the user.
<pre>
<code>
    $airpaymanager = $this->get('beinarovic_airpay_manager');

    if ($airpaymanager->paymentPassed() === true) {
        // Your code if payment succeeded.
    }
    // your code in case of fail.
</code>
</pre>

Also after running `paymentPassed()`, you can use `getPayment()`, which will return the `AirpayPayment` entity object or `false` if the payment was not retrieved.

## Status Receiver

This has to be placed into the action that is called from the Airpay. This URL is set up as the `Status URL` in the Airpay administration.

<pre>
<code>
//...
use Beinarovic\AirpayBundle\Entity\AirpayPayment;
//...

class SomeController extends Controller
{
    
    /**
     * @Route("/airpay/notification", name="your_airpay_nitifications")
     */
    public function airpayNofificationAction()
    {
        $airpaymanager = $this->get('beinarovic_airpay_manager');
        
        if ($airpaymanager->validate() === true) {
            $payment = $airpaymanager->getPayment();
            
            if ($airpaymanager->isSuccessful()) {
                
                $userid = $payment->getCustom();
                $amount = $payment->getAmount()/100;
                
                // Your payment management.
                
                // This will mark this payment as closed.
                $airpaymanager->closePayment();
            }
            
            if ($airpaymanager->isRefund()) {
                // Here you manage refunds.
            }
        }
        // ...
    }
</code>
</pre>
