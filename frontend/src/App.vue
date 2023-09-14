<template>
  <div>
    <div>
      <DxButtonSingle
        text="Add New Message"
        @click="createMessage"
      />
    </div>

    <br/>

    <DxDataGrid
        ref="grid"
        :data-source="store"
        :show-borders="true"
        :remote-operations="true"
    >
      <DxColumn
          data-field="uuid"
          data-type="string"
      />
      <DxColumn
          data-field="createdAt"
          data-type="string"
      />

      <DxColumn
          width="110"
          caption="Action"
          type="buttons"
      >
        <DxButton
            @click="showMessage"
            hint="View Message"
            icon="search"
        />
      </DxColumn>
      <DxPaging :page-size="12"/>
      <DxPager
          :show-page-size-selector="true"
          :allowed-page-sizes="[8, 12, 20]"
      />
    </DxDataGrid>

    <DxPopup
        v-model:visible="messagePopupVisible"
        :drag-enabled="false"
        :hide-on-outside-click="false"
        :show-close-button="false"
        :show-title="true"
        :width="300"
        :height="280"
        container=".dx-viewport"
        title="Information"
    >
      <DxToolbarItem
          widget="dxButton"
          toolbar="bottom"
          location="after"
          :options="closeButtonOptions"
      />
      <p>
        <strong>UUID</strong>: <span>{{ message.uuid }}</span>
      </p>
      <p>
        <strong>Created At:</strong> <span>{{ message.createdAt }}</span>
      </p>
      <p>
        <strong>Message:</strong> <span>{{message.message }}</span>
      </p>
    </DxPopup>

    <DxPopup
        v-model:visible="messageCreatePopupVisible"
        :drag-enabled="false"
        :hide-on-outside-click="false"
        :show-close-button="false"
        :show-title="true"
        :width="300"
        :height="280"
        container=".dx-viewport"
        title="Create Message"
    >
      <DxToolbarItem
          widget="dxButton"
          toolbar="bottom"
          location="before"
          :options="saveButtonOptions"
      />
      <DxToolbarItem
          widget="dxButton"
          toolbar="bottom"
          location="after"
          :options="closeButtonOptions"
      />
      <p>
        <strong>Message:</strong>
      </p>
      <p>
        <textarea rows="3" v-model="text" />
        <p v-if="validation">{{ validation }}</p>
      </p>
    </DxPopup>
  </div>
</template>

<script>
import {
  DxDataGrid, DxColumn, DxPaging, DxPager, DxButton
} from 'devextreme-vue/data-grid';
import DxButtonSingle from 'devextreme-vue/button';
import { DxPopup, DxPosition, DxToolbarItem } from 'devextreme-vue/popup';
import CustomStore from 'devextreme/data/custom_store';
import 'whatwg-fetch';

export default {
  components: {
    DxDataGrid,
    DxColumn,
    DxPaging,
    DxPager,
    DxButton,
    DxButtonSingle,
    DxPopup,
    DxPosition,
    DxToolbarItem,
  },
  methods: {
    createMessage() {
      this.messageCreatePopupVisible = true;
      this.text = '';
      this.validation = '';
    },

    saveMessage() {
      let that = this;

      that.validation = '';
      const data = new FormData();
      data.append('message', this.text);

      return fetch(`http://localhost:8080/api/store`, {
        mode: 'cors',
        method: 'POST',
        body: data
      })
          .then((response) => response.json())
          .then((data) => {
            if (!data.success) {
              that.validation = data.message;
            } else {
              that.$refs.grid.instance.refresh();
              that.messageCreatePopupVisible = false;
              that.text = '';
            }
          })
          .catch(() => {
            throw new Error('Data Loading Error');
          });

    },

    showMessage(e) {
      let uuid = e.row.data.uuid;
      let that = this;
      return fetch(`http://localhost:8080/api/message/${uuid}`, { mode: 'cors'})
          .then((response) => response.json())
          .then((data) => {
            that.messagePopupVisible = true;
            that.message = data;
          })
          .catch(() => {
            throw new Error('Data Loading Error');
          });
    }
  },
  data() {
    return {
      text: '',
      validation: '',
      message: {},
      messagePopupVisible: false,
      messageCreatePopupVisible: false,
      closeButtonOptions: {
        text: 'Close',
        onClick: () => {
          this.messagePopupVisible = false;
          this.messageCreatePopupVisible = false;
          this.message = {};
        },
      },
      saveButtonOptions: {
        icon: 'save',
        text: 'Save',
        onClick: () => {
          this.saveMessage();
        },
      },
      store: new CustomStore({
        key: 'uuid',
        load(loadOptions) {
          let params = '?';
          [
            'skip',
            'take',
            'sort',
          ].forEach((i) => {
            let that = this;
            if (i in loadOptions && (loadOptions[i] !== undefined && loadOptions[i] !== null && loadOptions[i] !== '')) {
              params += `${i}=${JSON.stringify(loadOptions[i])}&`;
            }
          });
          params = params.slice(0, -1);
          return fetch(`http://localhost:8080/api/messages${params}`, { mode: 'cors'})
              .then((response) => response.json())
              .then((data) => ({
                data: data.data,
                totalCount: data.totalCount,
                summary: data.summary,
                groupCount: data.groupCount,
              }))
              .catch(() => {
                throw new Error('Data Loading Error');
              });
        },
      }),
    }
  },
}
</script>