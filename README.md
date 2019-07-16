# Newsletter Plugin for Grav CMS
Newsletter Plugin provides a subscription functionality in order to subscribe to a newsletter.

![Newsletter Main Screen](https://github.com/mcspronko/grav-plugin-newsletter/raw/master/docs/newsletter-main.png "Newsletter Plugin")

## Installation

### Development

Use modman package manager to install the plugin.

From the Grav CMS project directory perform the commands
```bash
modman init
modman clone git@github.com:mcspronko/grav-plugin-newsletter.git
```

As a result, the new newsletter plugin should appear under user/plugins/newsletter directory. 

## Subscribers

### Admin

Navigate to the Admin -> Newsletter -> Audience page and click the "Add Subscriber" button. 
Fill in all required fields and hit the "Save" button.

![Add New Subscriber Modal](https://github.com/mcspronko/grav-plugin-newsletter/raw/master/docs/newsletter-add-subcriber-modal.png "Add New Subscriber Modal")


### Manually
In order to add a new subscriber, create a new Markdown file under <project_root>/user/data/newsletter/subscribers directory.

```

---
name: Max Pronko
email: max.pronko@gmail.com
is_subscribed: 1
created: 2019-07-10 11:10:03
---

```

From Admin -> Newsletter page you will see new subscriber record.



## Example of a Form Configuration
```yaml
form:
    name: newsletter
    action: /subscribe.json
    fields:
        -
            name: email
            size: field-size
            outerclasses: field-position
            placeholder: 'Your Email Here'
            type: email
            validate:
                required: true
    buttons:
        -
            type: submit
            classes: 'button submit'
            value: Subscribe
    process:
        -
            subscribe:
                fileprefix: subscriber-
                dateformat: Ymd-His-u
                extension: txt
                body: '{% include ''forms/data.txt.twig'' %}'
        -
            message: 'Thank you for your subscription!'
```
