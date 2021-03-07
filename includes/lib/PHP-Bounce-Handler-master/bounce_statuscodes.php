<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
#Generated by Make_statuscodes.php

#from http://www.iana.org/assignments/smtp-enhanced-status-codes/smtp-enhanced-status-codes-1.csv
#and smtp-enhanced-status-codes-3.csv

$status_code_classes['2']['title'] = "Success";  # [RFC3463] (Standards track)
$status_code_classes['2']['descr'] = "Success specifies that the DSN is reporting a positive delivery action. Detail sub-codes may provide notification of transformations required for delivery.";
$status_code_classes['4']['title'] = "Persistent Transient Failure";  # [RFC3463] (Standards track)
$status_code_classes['4']['descr'] = "A persistent transient failure is one in which the message as sent is valid, but persistence of some temporary condition has caused abandonment or delay of attempts to send the message. If this code accompanies a delivery failure report, sending in the future may be successful.";
$status_code_classes['5']['title'] = "Permanent Failure";  # [RFC3463] (Standards track)
$status_code_classes['5']['descr'] = "A permanent failure is one which is not likely to be resolved by resending the message in the current form. Some change to the message or the destination must be made for successful delivery.";

$status_code_subclasses['0.0']['title']  = "Other undefined Status";  # [RFC3463] (Standards Track)
$status_code_subclasses['0.0']['descr']  = "Other undefined status is the only undefined error code. It should be used for all errors for which only the class of the error is known.";
$status_code_subclasses['1.0']['title']  = "Other address status";  # [RFC3463] (Standards Track)
$status_code_subclasses['1.0']['descr']  = "Something about the address specified in the message caused this DSN.";
$status_code_subclasses['1.1']['title']  = "Bad destination mailbox address";  # [RFC3463] (Standards Track)
$status_code_subclasses['1.1']['descr']  = "The mailbox specified in the address does not exist. For Internet mail names, this means the address portion to the left of the \"@\" sign is invalid. This code is only useful for permanent failures.";
$status_code_subclasses['1.2']['title']  = "Bad destination system address";  # [RFC3463] (Standards Track)
$status_code_subclasses['1.2']['descr']  = "The destination system specified in the address does not exist or is incapable of accepting mail. For Internet mail names, this means the address portion to the right of the \"@\" is invalid for mail. This code is only useful for permanent failures.";
$status_code_subclasses['1.3']['title']  = "Bad destination mailbox address syntax";  # [RFC3463] (Standards Track)
$status_code_subclasses['1.3']['descr']  = "The destination address was syntactically invalid. This can apply to any field in the address. This code is only useful for permanent failures.";
$status_code_subclasses['1.4']['title']  = "Destination mailbox address ambiguous";  # [RFC3463] (Standards Track)
$status_code_subclasses['1.4']['descr']  = "The mailbox address as specified matches one or more recipients on the destination system. This may result if a heuristic address mapping algorithm is used to map the specified address to a local mailbox name.";
$status_code_subclasses['1.5']['title']  = "Destination address valid";  # [RFC3463] (Standards Track)
$status_code_subclasses['1.5']['descr']  = "This mailbox address as specified was valid. This status code should be used for positive delivery reporting.";
$status_code_subclasses['1.6']['title']  = "Destination mailbox has moved, No forwarding address";  # [RFC3463] (Standards Track)
$status_code_subclasses['1.6']['descr']  = "The mailbox address provided was at one time valid, but mail is no longer being accepted for that address. This code is only useful for permanent failures.";
$status_code_subclasses['1.7']['title']  = "Bad sender's mailbox address syntax";  # [RFC3463] (Standards Track)
$status_code_subclasses['1.7']['descr']  = "The sender's address was syntactically invalid. This can apply to any field in the address.";
$status_code_subclasses['1.8']['title']  = "Bad sender's system address";  # [RFC3463] (Standards Track)
$status_code_subclasses['1.8']['descr']  = "The sender's system specified in the address does not exist or is incapable of accepting return mail. For domain names, this means the address portion to the right of the \"@\" is invalid for mail.";
$status_code_subclasses['1.9']['title']  = "Message relayed to non-compliant mailer";  # [RFC3886] (Standards Track)
$status_code_subclasses['1.9']['descr']  = "The mailbox address specified was valid, but the message has been relayed to a system that does not speak this protocol; no further information can be provided.";
$status_code_subclasses['2.0']['title']  = "Other or undefined mailbox status";  # [RFC3463] (Standards Track)
$status_code_subclasses['2.0']['descr']  = "The mailbox exists, but something about the destination mailbox has caused the sending of this DSN.";
$status_code_subclasses['2.1']['title']  = "Mailbox disabled, not accepting messages";  # [RFC3463] (Standards Track)
$status_code_subclasses['2.1']['descr']  = "The mailbox exists, but is not accepting messages. This may be a permanent error if the mailbox will never be re-enabled or a transient error if the mailbox is only temporarily disabled.";
$status_code_subclasses['2.2']['title']  = "Mailbox full";  # [RFC3463] (Standards Track)
$status_code_subclasses['2.2']['descr']  = "The mailbox is full because the user has exceeded a per-mailbox administrative quota or physical capacity. The general semantics implies that the recipient can delete messages to make more space available. This code should be used as a persistent transient failure.";
$status_code_subclasses['2.3']['title']  = "Message length exceeds administrative limit";  # [RFC3463] (Standards Track)
$status_code_subclasses['2.3']['descr']  = "A per-mailbox administrative message length limit has been exceeded. This status code should be used when the per-mailbox message length limit is less than the general system limit. This code should be used as a permanent failure.";
$status_code_subclasses['2.4']['title']  = "Mailing list expansion problem";  # [RFC3463] (Standards Track)
$status_code_subclasses['2.4']['descr']  = "The mailbox is a mailing list address and the mailing list was unable to be expanded. This code may represent a permanent failure or a persistent transient failure.";
$status_code_subclasses['3.0']['title']  = "Other or undefined mail system status";  # [RFC3463] (Standards Track)
$status_code_subclasses['3.0']['descr']  = "The destination system exists and normally accepts mail, but something about the system has caused the generation of this DSN.";
$status_code_subclasses['3.1']['title']  = "Mail system full";  # [RFC3463] (Standards Track)
$status_code_subclasses['3.1']['descr']  = "Mail system storage has been exceeded. The general semantics imply that the individual recipient may not be able to delete material to make room for additional messages. This is useful only as a persistent transient error.";
$status_code_subclasses['3.2']['title']  = "System not accepting network messages";  # [RFC3463] (Standards Track)
$status_code_subclasses['3.2']['descr']  = "The host on which the mailbox is resident is not accepting messages. Examples of such conditions include an immanent shutdown, excessive load, or system maintenance. This is useful for both permanent and persistent transient errors.";
$status_code_subclasses['3.3']['title']  = "System not capable of selected features";  # [RFC3463] (Standards Track)
$status_code_subclasses['3.3']['descr']  = "Selected features specified for the message are not supported by the destination system. This can occur in gateways when features from one domain cannot be mapped onto the supported feature in another.";
$status_code_subclasses['3.4']['title']  = "Message too big for system";  # [RFC3463] (Standards Track)
$status_code_subclasses['3.4']['descr']  = "The message is larger than per-message size limit. This limit may either be for physical or administrative reasons. This is useful only as a permanent error.";
$status_code_subclasses['3.5']['title']  = "System incorrectly configured";  # [RFC3463] (Standards Track)
$status_code_subclasses['3.5']['descr']  = "The system is not configured in a manner that will permit it to accept this message.";
$status_code_subclasses['3.6']['title']  = "Requested priority was changed";  # [RFC6710] (Standards Track)
$status_code_subclasses['3.6']['descr']  = "The message was accepted for relay/delivery, but the requested priority (possibly the implied default) was not honoured. The human readable text after the status code contains the new priority, followed by SP (space) and explanatory human readable text.";
$status_code_subclasses['4.0']['title']  = "Other or undefined network or routing status";  # [RFC3463] (Standards Track)
$status_code_subclasses['4.0']['descr']  = "Something went wrong with the networking, but it is not clear what the problem is, or the problem cannot be well expressed with any of the other provided detail codes.";
$status_code_subclasses['4.1']['title']  = "No answer from host";  # [RFC3463] (Standards Track)
$status_code_subclasses['4.1']['descr']  = "The outbound connection attempt was not answered, because either the remote system was busy, or was unable to take a call. This is useful only as a persistent transient error.";
$status_code_subclasses['4.2']['title']  = "Bad connection";  # [RFC3463] (Standards Track)
$status_code_subclasses['4.2']['descr']  = "The outbound connection was established, but was unable to complete the message transaction, either because of time-out, or inadequate connection quality. This is useful only as a persistent transient error.";
$status_code_subclasses['4.3']['title']  = "Directory server failure";  # [RFC3463] (Standards Track)
$status_code_subclasses['4.3']['descr']  = "The network system was unable to forward the message, because a directory server was unavailable. This is useful only as a persistent transient error. The inability to connect to an Internet DNS server is one example of the directory server failure error.";
$status_code_subclasses['4.4']['title']  = "Unable to route";  # [RFC3463] (Standards Track)
$status_code_subclasses['4.4']['descr']  = "The mail system was unable to determine the next hop for the message because the necessary routing information was unavailable from the directory server. This is useful for both permanent and persistent transient errors. A DNS lookup returning only an SOA (Start of Administration) record for a domain name is one example of the unable to route error.";
$status_code_subclasses['4.5']['title']  = "Mail system congestion";  # [RFC3463] (Standards Track)
$status_code_subclasses['4.5']['descr']  = "The mail system was unable to deliver the message because the mail system was congested. This is useful only as a persistent transient error.";
$status_code_subclasses['4.6']['title']  = "Routing loop detected";  # [RFC3463] (Standards Track)
$status_code_subclasses['4.6']['descr']  = "A routing loop caused the message to be forwarded too many times, either because of incorrect routing tables or a user- forwarding loop. This is useful only as a persistent transient error.";
$status_code_subclasses['4.7']['title']  = "Delivery time expired";  # [RFC3463] (Standards Track)
$status_code_subclasses['4.7']['descr']  = "The message was considered too old by the rejecting system, either because it remained on that host too long or because the time-to-live value specified by the sender of the message was exceeded. If possible, the code for the actual problem found when delivery was attempted should be returned rather than this code.";
$status_code_subclasses['5.0']['title']  = "Other or undefined protocol status";  # [RFC3463] (Standards Track)
$status_code_subclasses['5.0']['descr']  = "Something was wrong with the protocol necessary to deliver the message to the next hop and the problem cannot be well expressed with any of the other provided detail codes.";
$status_code_subclasses['5.1']['title']  = "Invalid command";  # [RFC3463] (Standards Track)
$status_code_subclasses['5.1']['descr']  = "A mail transaction protocol command was issued which was either out of sequence or unsupported. This is useful only as a permanent error.";
$status_code_subclasses['5.2']['title']  = "Syntax error";  # [RFC3463] (Standards Track)
$status_code_subclasses['5.2']['descr']  = "A mail transaction protocol command was issued which could not be interpreted, either because the syntax was wrong or the command is unrecognized. This is useful only as a permanent error.";
$status_code_subclasses['5.3']['title']  = "Too many recipients";  # [RFC3463] (Standards Track)
$status_code_subclasses['5.3']['descr']  = "More recipients were specified for the message than could have been delivered by the protocol. This error should normally result in the segmentation of the message into two, the remainder of the recipients to be delivered on a subsequent delivery attempt. It is included in this list in the event that such segmentation is not possible.";
$status_code_subclasses['5.4']['title']  = "Invalid command arguments";  # [RFC3463] (Standards Track)
$status_code_subclasses['5.4']['descr']  = "A valid mail transaction protocol command was issued with invalid arguments, either because the arguments were out of range or represented unrecognized features. This is useful only as a permanent error.";
$status_code_subclasses['5.5']['title']  = "Wrong protocol version";  # [RFC3463] (Standards Track)
$status_code_subclasses['5.5']['descr']  = "A protocol version mis-match existed which could not be automatically resolved by the communicating parties.";
$status_code_subclasses['5.6']['title']  = "Authentication Exchange line is too long";  # [RFC4954] (Standards Track)
$status_code_subclasses['5.6']['descr']  = "This enhanced status code SHOULD be returned when the server fails the AUTH command due to the client sending a [BASE64] response which is longer than the maximum buffer size available for the currently selected SASL mechanism. This is useful for both permanent and persistent transient errors.";
$status_code_subclasses['6.0']['title']  = "Other or undefined media error";  # [RFC3463] (Standards Track)
$status_code_subclasses['6.0']['descr']  = "Something about the content of a message caused it to be considered undeliverable and the problem cannot be well expressed with any of the other provided detail codes.";
$status_code_subclasses['6.1']['title']  = "Media not supported";  # [RFC3463] (Standards Track)
$status_code_subclasses['6.1']['descr']  = "The media of the message is not supported by either the delivery protocol or the next system in the forwarding path. This is useful only as a permanent error.";
$status_code_subclasses['6.2']['title']  = "Conversion required and prohibited";  # [RFC3463] (Standards Track)
$status_code_subclasses['6.2']['descr']  = "The content of the message must be converted before it can be delivered and such conversion is not permitted. Such prohibitions may be the expression of the sender in the message itself or the policy of the sending host.";
$status_code_subclasses['6.3']['title']  = "Conversion required but not supported";  # [RFC3463] (Standards Track)
$status_code_subclasses['6.3']['descr']  = "The message content must be converted in order to be forwarded but such conversion is not possible or is not practical by a host in the forwarding path. This condition may result when an ESMTP gateway supports 8bit transport but is not able to downgrade the message to 7 bit as required for the next hop.";
$status_code_subclasses['6.4']['title']  = "Conversion with loss performed";  # [RFC3463] (Standards Track)
$status_code_subclasses['6.4']['descr']  = "This is a warning sent to the sender when message delivery was successfully but when the delivery required a conversion in which some data was lost. This may also be a permanent error if the sender has indicated that conversion with loss is prohibited for the message.";
$status_code_subclasses['6.5']['title']  = "Conversion Failed";  # [RFC3463] (Standards Track)
$status_code_subclasses['6.5']['descr']  = "A conversion was required but was unsuccessful. This may be useful as a permanent or persistent temporary notification.";
$status_code_subclasses['6.6']['title']  = "Message content not available";  # [RFC4468] (Standards Track)
$status_code_subclasses['6.6']['descr']  = "The message content could not be fetched from a remote system. This may be useful as a permanent or persistent temporary notification.";
$status_code_subclasses['6.7']['title']  = "Non-ASCII addresses not permitted for that sender/recipient";  # [RFC6531] (Standards track)
$status_code_subclasses['6.7']['descr']  = "This indicates the reception of a MAIL or RCPT command that non-ASCII addresses are not permitted";
$status_code_subclasses['6.8']['title']  = "UTF-8 string reply is required, but not permitted by the SMTP client";  # [RFC6531] (Standards track)
$status_code_subclasses['6.8']['descr']  = "This indicates that a reply containing a UTF-8 string is required to show the mailbox name, but that form of response is not permitted by the SMTP client.";
$status_code_subclasses['6.9']['title']  = "UTF-8 header message cannot be transferred to one or more recipients, so the message must be rejected";  # [RFC6531] (Standards track)
$status_code_subclasses['6.9']['descr']  = "This indicates that transaction failed after the final \".\" of the DATA command.";
$status_code_subclasses['6.10']['title'] = "";  # [RFC6531] (Standards track)
$status_code_subclasses['6.10']['descr'] = "This is a duplicate of X.6.8 and is thus deprecated.";
$status_code_subclasses['7.0']['title']  = "Other or undefined security status";  # [RFC3463] (Standards Track)
$status_code_subclasses['7.0']['descr']  = "Something related to security caused the message to be returned, and the problem cannot be well expressed with any of the other provided detail codes. This status code may also be used when the condition cannot be further described because of security policies in force.";
$status_code_subclasses['7.1']['title']  = "Delivery not authorized, message refused";  # [RFC3463] (Standards Track)
$status_code_subclasses['7.1']['descr']  = "The sender is not authorized to send to the destination. This can be the result of per-host or per-recipient filtering. This memo does not discuss the merits of any such filtering, but provides a mechanism to report such. This is useful only as a permanent error.";
$status_code_subclasses['7.2']['title']  = "Mailing list expansion prohibited";  # [RFC3463] (Standards Track)
$status_code_subclasses['7.2']['descr']  = "The sender is not authorized to send a message to the intended mailing list. This is useful only as a permanent error.";
$status_code_subclasses['7.3']['title']  = "Security conversion required but not possible";  # [RFC3463] (Standards Track)
$status_code_subclasses['7.3']['descr']  = "A conversion from one secure messaging protocol to another was required for delivery and such conversion was not possible. This is useful only as a permanent error.";
$status_code_subclasses['7.4']['title']  = "Security features not supported";  # [RFC3463] (Standards Track)
$status_code_subclasses['7.4']['descr']  = "A message contained security features such as secure authentication that could not be supported on the delivery protocol. This is useful only as a permanent error.";
$status_code_subclasses['7.5']['title']  = "Cryptographic failure";  # [RFC3463] (Standards Track)
$status_code_subclasses['7.5']['descr']  = "A transport system otherwise authorized to validate or decrypt a message in transport was unable to do so because necessary information such as key was not available or such information was invalid.";
$status_code_subclasses['7.6']['title']  = "Cryptographic algorithm not supported";  # [RFC3463] (Standards Track)
$status_code_subclasses['7.6']['descr']  = "A transport system otherwise authorized to validate or decrypt a message was unable to do so because the necessary algorithm was not supported.";
$status_code_subclasses['7.7']['title']  = "Message integrity failure";  # [RFC3463] (Standards Track)
$status_code_subclasses['7.7']['descr']  = "A transport system otherwise authorized to validate a message was unable to do so because the message was corrupted or altered. This may be useful as a permanent, transient persistent, or successful delivery code.";
$status_code_subclasses['7.8']['title']  = "Authentication credentials invalid";  # [RFC4954] (Standards Track)
$status_code_subclasses['7.8']['descr']  = "This response to the AUTH command indicates that the authentication failed due to invalid or insufficient authentication credentials. In this case, the client SHOULD ask the user to supply new credentials (such as by presenting a password dialog box).";
$status_code_subclasses['7.9']['title']  = "Authentication mechanism is too weak";  # [RFC4954] (Standards Track)
$status_code_subclasses['7.9']['descr']  = "This response to the AUTH command indicates that the selected authentication mechanism is weaker than server policy permits for that user. The client SHOULD retry with a new authentication mechanism.";
$status_code_subclasses['7.10']['title'] = "Encryption Needed";  # [RFC5248] (Best current practice)
$status_code_subclasses['7.10']['descr'] = "This indicates that external strong privacy layer is needed in order to use the requested authentication mechanism. This is primarily intended for use with clear text authentication mechanisms. A client which receives this may activate a security layer such as TLS prior to authenticating, or attempt to use a stronger mechanism.";
$status_code_subclasses['7.11']['title'] = "Encryption required for requested authentication mechanism";  # [RFC4954] (Standards Track)
$status_code_subclasses['7.11']['descr'] = "This response to the AUTH command indicates that the selected authentication mechanism may only be used when the underlying SMTP connection is encrypted. Note that this response code is documented here for historical purposes only. Modern implementations SHOULD NOT advertise mechanisms that are not permitted due to lack of encryption, unless an encryption layer of sufficient strength is currently being employed.";
$status_code_subclasses['7.12']['title'] = "A password transition is needed";  # [RFC4954] (Standards Track)
$status_code_subclasses['7.12']['descr'] = "This response to the AUTH command indicates that the user needs to transition to the selected authentication mechanism. This is typically done by authenticating once using the [PLAIN] authentication mechanism. The selected mechanism SHOULD then work for authentications in subsequent sessions.";
$status_code_subclasses['7.13']['title'] = "User Account Disabled";  # [RFC5248] (Best current practice)
$status_code_subclasses['7.13']['descr'] = "Sometimes a system administrator will have to disable a user's account (e.g., due to lack of payment, abuse, evidence of a break-in attempt, etc). This error code occurs after a successful authentication to a disabled account. This informs the client that the failure is permanent until the user contacts their system administrator to get the account re-enabled. It differs from a generic authentication failure where the client's best option is to present the passphrase entry dialog in case the user simply mistyped their passphrase.";
$status_code_subclasses['7.14']['title'] = "Trust relationship required";  # [RFC5248] (Best current practice)
$status_code_subclasses['7.14']['descr'] = "The submission server requires a configured trust relationship with a third-party server in order to access the message content. This value replaces the prior use of X.7.8 for this error condition. thereby updating [RFC4468].";
$status_code_subclasses['7.15']['title'] = "Priority Level is too low";  # [RFC6710] (Standards Track)
$status_code_subclasses['7.15']['descr'] = "The specified priority level is below the lowest priority acceptable for the receiving SMTP server. This condition might be temporary, for example the server is operating in a mode where only higher priority messages are accepted for transfer and delivery, while lower priority messages are rejected.";
$status_code_subclasses['7.16']['title'] = "Message is too big for the specified priority";  # [RFC6710] (Standards Track)
$status_code_subclasses['7.16']['descr'] = "The message is too big for the specified priority. This condition might be temporary, for example the server is operating in a mode where only higher priority messages below certain size are accepted for transfer and delivery.";
$status_code_subclasses['7.17']['title'] = "Mailbox owner has changed";  # [RFC-ietf-appsawg-rrvs-header-field-10] (Standards Track)
$status_code_subclasses['7.17']['descr'] = "This status code is returned when a message is received with a Require-Recipient-Valid-Since field or RRVS extension and the receiving system is able to determine that the intended recipient mailbox has not been under continuous ownership since the specified date.";
$status_code_subclasses['7.18']['title'] = "Domain owner has changed";  # [RFC-ietf-appsawg-rrvs-header-field-10] (Standards Track)
$status_code_subclasses['7.18']['descr'] = "This status code is returned when a message is received with a Require-Recipient-Valid-Since field or RRVS extension and the receiving system wishes to disclose that the owner of the domain name of the recipient has changed since the specified date.";
$status_code_subclasses['7.19']['title'] = "RRVS test cannot be completed";  # [RFC-ietf-appsawg-rrvs-header-field-10] (Standards Track)
$status_code_subclasses['7.19']['descr'] = "This status code is returned when a message is received with a Require-Recipient-Valid-Since field or RRVS extension and the receiving system cannot complete the requested evaluation because the required timestamp was not recorded. The message originator needs to decide whether to reissue the message without RRVS protection.";