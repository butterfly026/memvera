<template>
    <div class="attachment-wrapper">
        <attachment-item
            v-for="(attachment, index) in attachments"
            :key="index"
            :attachment="attachment"
            :input-name="inputName + '[' + index + ']'"
            @onRemoveAttachment="removeAttachment($event)"
        ></attachment-item>

        <label class="add-attachment-link" @click="addAttachment">
            <i class="ri-attachment-line"></i>
            Add Attachment
        </label>
    </div>
</template>

<script>
import AttachmentItem from "@/Components/attachment/attachment-item.vue";
export default {
    components: {
        AttachmentItem
    },
    props: {
        data: {
            type: [Array, String],
            required: false,
            default: () => []
        },

        inputName: {
            type: String,
            required: false,
            default: "attachments"
        }
    },

    data: function() {
        return {
            attachmentCount: 0,
            attachments: []
        };
    },

    created() {
        let self = this;
        this.data.forEach(function(attachment) {
            attachment.isNew = false;
            self.attachments.push(attachment);
            self.attachmentCount++;
        });
    },

    methods: {
        addAttachment() {
            this.attachmentCount++;
            this.attachments.push({
                id: "attachment_" + this.attachmentCount,
                isNew: true
            });
        },

        removeAttachment(attachment) {
            let index = this.attachments.indexOf(attachment);
            this.attachments.splice(index, 1);
        }
    }
};
</script>
