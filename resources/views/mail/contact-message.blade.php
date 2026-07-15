@component('mail::message')
# Contact form message

**From:** {{ $senderName }} ({{ $senderEmail }})
**Subject:** {{ $contactSubject }}

---

{{ $body }}

---

Reply to this email to answer {{ $senderName }} directly — the reply-to is already
set to their address.

@endcomponent
