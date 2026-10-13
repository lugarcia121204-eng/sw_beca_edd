<template>
	<div class="flex flex-col gap-4 md:gap-0 md:flex-row md:items-center md:justify-between mb-6">
		<slot />

		<BaseButton color="lightDark" label="Agregar red social" @click="addSocialLink" :icon="mdiPlus" />
	</div>

	<div v-if="socialLinks.length > 0" class="border rounded-lg">
		<table>
			<thead>
				<tr>
					<th>Url</th>
					<th>Tipo</th>
					<th>Acciones</th>
				</tr>
			</thead>

			<tbody>
				<tr v-for="(socialLink, index) in socialLinks" :key="index">
					<td data-label="Url" class="md:max-w-full">
						<div class="flex flex-col flex-wrap gap-3">
							<FormControl v-model="socialLink.url" type="text"
								placeholder="Ej: https://www.instagram.com/" :icon="mdiLinkVariant" />
							<div class="max-h-2.5">
								<InputError :message="props.errors?.[`social_links.${index}.url`]" />
							</div>
						</div>
					</td>
					<td data-label="Tipo" class="md:w-72">
						<div class="flex flex-col flex-wrap gap-3">
							<FormControl type="select" v-model="socialLink.type" valueSelect="value"
								valueOption="label" :options="socialTypeOptions" :icon="iconFor(socialLink.type)" />
							<div class="max-h-2.5">
								<InputError :message="props.errors?.[`social_links.${index}.type`]" />
							</div>
						</div>
					</td>
					<td data-label="Acciones">
						<div class="flex flex-col flex-wrap gap-3">
							<BaseButtons>
								<BaseButton @click="removeSocialLink(index)" color="danger" small
									:icon="mdiTrashCan" />
							</BaseButtons>
							<div class="max-h-2.5"></div>
						</div>
					</td>
				</tr>
			</tbody>
		</table>
	</div>

	<CardBoxComponentEmpty v-else />
</template>

<script setup>
import BaseButton from '@/components/BaseButton.vue';
import BaseButtons from '@/components/BaseButtons.vue';
import CardBoxComponentEmpty from '@/components/CardBoxComponentEmpty.vue';
import FormControl from '@/components/FormControl.vue';
import InputError from './InputError.vue';
import {
	mdiPlus,
	mdiInstagram, mdiFacebook, mdiTwitter, mdiLinkedin, mdiYoutube, mdiWeb, mdiMusicNote,
	mdiTrashCan,
	mdiLinkVariant,
} from '@mdi/js'
import { useSocialLinks } from '@/Hooks/useSocialLinks';

const socialLinks = defineModel({
	default: [],
});

const props = defineProps({
	errors: {
		type: Object,
		default: () => ({}),
		required: false
	}
});

const { addSocialLink, removeSocialLink } = useSocialLinks(socialLinks);

const socialTypeOptions = [
	{ value: 'instagram', label: 'Instagram' },
	{ value: 'facebook', label: 'Facebook' },
	{ value: 'x', label: 'X (Twitter)' },
	{ value: 'tiktok', label: 'TikTok' },
	{ value: 'linkedin', label: 'LinkedIn' },
	{ value: 'youtube', label: 'YouTube' },
]

const iconFor = (type) => ({
	instagram: mdiInstagram,
	facebook: mdiFacebook,
	x: mdiTwitter,
	tiktok: mdiMusicNote,
	linkedin: mdiLinkedin,
	youtube: mdiYoutube,
}[type] ?? mdiWeb)
</script>

