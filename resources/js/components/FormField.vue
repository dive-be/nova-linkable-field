<template>
    <default-field :field="field" :errors="errors" :show-help-text="showHelpText">
        <template slot="field">
            <label class="mb-2 block">Type</label>
            <select v-model="type" class="w-full form-control form-input form-input-bordered">
                <option value="manual">Manual URL</option>
                <option value="linked">Linked {{ field.linked_name }}</option>
            </select>
            <label class="mb-2 mt-4 block">Value</label>
            <div v-if="type === 'manual'">
                <div v-if="!field.translatable">
                    <input
                        :id="field.name"
                        type="text"
                        class="w-full form-control form-input form-input-bordered"
                        :class="errorClasses"
                        :placeholder="field.name"
                        v-model="value"
                    />
                </div>
                <div v-if="field.translatable">
                    <div v-for="(language, code) in field.locales" class="flex items-center">
                        <label class="mb-4 w-1/6 mt-4 block mr-3 font-bold text-sm">{{ language }}</label>
                        <input type="text" v-model="manual_value[code]" class="w-full form-control form-input form-input-bordered" />
                    </div>
                </div>
            </div>
            <select v-if="type === 'linked'" class="w-full form-control form-input form-input-bordered" v-model="linked_id">
                <option v-for="(value, id) in field.linked_values" :value="id">
                    {{ value }}
                </option>
            </select>
        </template>
    </default-field>
</template>

<script>
import {FormField, HandlesValidationErrors} from 'laravel-nova'

export default {
    mixins: [FormField, HandlesValidationErrors],

    props: [
        'resourceName', 'resourceId', 'field',
        'initial_type', 'initial_id'
    ],

    data() {
        return {
            type: (this.field.initial_type == null || this.field.initial_type === '') ? 'manual' : 'linked',
            linked_id: this.field.initial_id,
            manual_value: this.field.initial_manual_value
        }
    },

    methods: {
        fill(formData) {
            formData.append(this.field.attribute + "-type", this.type);
            if (this.type === 'manual') {
                formData.append(this.field.attribute, this.field.translatable
                    ? JSON.stringify(this.manual_value)
                    : this.manual_value || ''
                );
            } else {
                formData.append(this.field.attribute, this.linked_id)
            }
        },
    },
}
</script>
