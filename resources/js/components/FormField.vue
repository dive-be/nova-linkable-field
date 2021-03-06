<template>
    <default-field :field="field" :errors="errors" :show-help-text="showHelpText">
        <template slot="field">
            <label class="mb-2 block">Type</label>
            <select v-model="type" class="w-full form-control form-input form-input-bordered">
                <option value="manual">Manual URL</option>
                <option value="linked">Linked {{ field.linkedName }}</option>
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
                        <input type="text" v-model="manualValue[code]" class="w-full form-control form-input form-input-bordered" />
                    </div>
                </div>
            </div>
            <select v-if="type === 'linked'" class="w-full form-control form-input form-input-bordered" v-model="linkedId">
                <option v-for="(value, id) in field.linkedValues" :value="id">
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
        'resourceName', 'resourceId', 'field'
    ],

    data() {
        return {
            type: this.field.initialType,
            linkedId: this.field.initialId,
            manualValue: this.field.initialManualValue
        }
    },

    methods: {
        fill(formData) {
            formData.append(this.field.attribute + "-type", this.type);
            if (this.type === 'manual') {
                formData.append(this.field.attribute, this.field.translatable
                    ? JSON.stringify(this.manualValue)
                    : this.manualValue || ''
                );
            } else {
                formData.append(this.field.attribute, this.linkedId)
            }
        },
    },
}
</script>
