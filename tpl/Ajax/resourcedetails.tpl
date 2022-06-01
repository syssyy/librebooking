<div id="resourceDetailsPopup">
    {assign var=h4Style value=""}
    {if !empty($color)}
        {assign var=h4Style value=" style=\"background-color:{$color};color:{$textColor};padding:5px 3px;\""}
    {/if}
    <div class="resourceNameTitle">
        <h4 {$h4Style}>{$resourceName}</h4>
        <a href="#" class="visible-sm-inline-block hideResourceDetailsPopup">{translate key=Close}</a>
        <div class="clearfix"></div>
    </div>
    {assign var=class value='col-xs-6'}

    {if $imageUrl neq ''}
        {assign var=class value='col-xs-5'}

        <div class="resourceImage col-xs-2">
            <div class="owl-carousel owl-theme">
                <div class="item">
                    <img src="{resource_image image=$imageUrl}" alt="{$resourceName|escape}" class="image" />
                </div>
                {foreach from=$images item=image}
                    <div class="item">
                        <img src="{resource_image image=$image}" alt="{$resourceName|escape}" class="image" />
                    </div>
                {/foreach}
            </div>
        </div>
    {/if}
    <div class="description {$class}">
        <span class="bold">{translate key=Description}</span>
        {if $description neq ''}
            {$description|html_entity_decode|url2link|nl2br}
        {else}
            {translate key=NoDescriptionLabel}
        {/if}
        <br/>
        <span class="bold">{translate key=Notes}</span>
        {if $notes neq ''}
            {$notes|html_entity_decode|url2link|nl2br}
        {else}
            {translate key=NoNotesLabel}
        {/if}
        <br/>
        <span class="bold">{translate key=Contact}</span>
        {if $contactInformation neq ''}
            {$contactInformation}
        {else}
            {translate key=NoContactLabel}
        {/if}
        <br/>
        <span class="bold">{translate key=Location}</span>
        {if $locationInformation neq ''}
            {$locationInformation}
        {else}
            {translate key=NoLocationLabel}
        {/if}
        <br/>
        <span class="bold">{translate key=ResourceType}</span>
        {if $resourceType neq ''}
            {$resourceType}
        {else}
            {translate key=NoResourceTypeLabel}
        {/if}
        {if $Attributes|default:array()|count > 0}
            {foreach from=$Attributes item=attribute}
                <div>
                    {control type="AttributeControl" attribute=$attribute readonly=true}
                </div>
            {/foreach}
        {/if}
        {if $ResourceTypeAttributes && $ResourceTypeAttributes|default:array()|count > 0}
            {foreach from=$ResourceTypeAttributes item=attribute}
                <div>
                    {control type="AttributeControl" attribute=$attribute readonly=true}
                </div>
            {/foreach}
        {/if}
    </div>
    <div class="attributes {$class}">
        <div>
            {if $minimumDuration neq ''}
                {translate key='ResourceMinLength' args=$minimumDuration}
            {else}
                {translate key='ResourceMinLengthNone'}
            {/if}
        </div>
        <div>
            {if $maximumDuration neq ''}
                {translate key='ResourceMaxLength' args=$maximumDuration}
            {else}
                {translate key='ResourceMaxLengthNone'}
            {/if}
        </div>
        <div>
            {if $requiresApproval}
                {translate key='ResourceRequiresApproval'}
            {else}
                {translate key='ResourceRequiresApprovalNone'}
            {/if}
        </div>
        <div>
            {if $minimumNotice neq ''}
                {translate key='ResourceMinNotice' args=$minimumNotice}
            {else}
                {translate key='ResourceMinNoticeNone'}
            {/if}
        </div>
        <div>
            {if $maximumNotice neq ''}
                {translate key='ResourceMaxNotice' args=$maximumNotice}
            {else}
                {translate key='ResourceMaxNoticeNone'}
            {/if}
        </div>
        <div>
            {if $allowMultiday}
                {translate key='ResourceAllowMultiDay'}
            {else}
                {translate key='ResourceNotAllowMultiDay'}
            {/if}
        </div>
        <div>
            {if $maxParticipants neq ''}
                {translate key='ResourceCapacity' args=$maxParticipants}
            {else}
                {translate key='ResourceCapacityNone'}
            {/if}
        </div>

        {if $autoReleaseMinutes neq ''}
            <div>
                {translate key='AutoReleaseNotification' args=$autoReleaseMinutes}
            </div>
        {/if}
        {if $isCheckInEnabled neq ''}
            <div>
                {translate key='RequiresCheckInNotification'}
            </div>
        {/if}

        {if $creditsEnabled}
            <div>
                {translate key=CreditUsagePerSlot args=$offPeakCredits}
            </div>
            <div>
                {translate key=PeakCreditUsagePerSlot args=$peakCredits}
            </div>
        {/if}
    </div>
    <div style="clearfix">&nbsp;</div>
</div>
