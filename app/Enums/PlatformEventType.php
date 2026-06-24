<?php

namespace App\Enums;

enum PlatformEventType: string
{
    case OutboundSubmitted = 'outbound.submitted';
    case OutboundAccepted = 'outbound.accepted';
    case OutboundRejected = 'outbound.rejected';
    case InboundReceived = 'inbound.received';
}
