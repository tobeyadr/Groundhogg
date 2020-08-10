import React, { useEffect, useRef } from 'react'
import { IconButton } from '../../components/Buttons/Buttons'
import { connect } from 'react-redux'
import {
  clearItems,
  fetchContacts,
  fetchMoreContacts,
  changeContext,
  resetQuery,
  updateQuery, clearState,
} from '../../actions/contactListActions'
import { ListTable } from '../../components/ListTable/ListTable'
import { FaIcon, TagPicker } from '../../components/basic-components'
import { Badge, Button, Form } from 'react-bootstrap'
import moment from 'moment'
import Select from 'react-select'
import './style.scss'
import { number_format } from '../../functions'

const optinStatusMap = {
  1: <Badge variant={ 'secondary' }>{ 'Unconfirmed' }</Badge>,
  2: <Badge variant={ 'success' }>{ 'Confirmed' }</Badge>,
  3: <Badge variant={ 'dark' }>{ 'Unsubscribed' }</Badge>,
  4: <Badge variant={ 'success' }>{ 'Weekly' }</Badge>,
  5: <Badge variant={ 'success' }>{ 'Monthly' }</Badge>,
  6: <Badge variant={ 'danger' }>{ 'Bounced' }</Badge>,
  7: <Badge variant={ 'danger' }>{ 'Spam' }</Badge>,
  8: <Badge variant={ 'danger' }>{ 'Complained' }</Badge>,
}

const optinStatusFilters = [
  { label: 'Unconfirmed', value: 1 },
  { label: 'Confirmed', value: 2 },
  { label: 'Unsubscribed', value: 3 },
  { label: 'Weekly', value: 4 },
  { label: 'Monthly', value: 5 },
  { label: 'Bounced', value: 6 },
  { label: 'Spam', value: 7 },
  { label: 'Complained', value: 8 },
]

const columns = [
  {
    id: 'id',
    name: <input
      type={ 'checkbox' }
      className={ 'big-checkbox' }
      name={ 'ID[]' }
      readOnly={ true }
    />,
    render: ({ item }) => {
      return <input
        type={ 'checkbox' }
        className={ 'big-checkbox' }
        name={ 'ID[' + item.ID + ']' }
        readOnly={ true }
      />
    },
  },
  {
    id: 'picture',
    name: '',
    render: ({ item }) => {
      return <img
        className={ 'gravatar' } src={ item.data.gravatar }
        alt={ 'gravatar' }/>
    },
  },
  {
    id: 'name',
    name: 'Name',
    render: ({ item }) => {

      const { data, user } = item

      return ( <div className={ 'name-details' }>
        { data.first_name + ' ' + data.last_name }
        { user && <div className={ 'user-details' }>
          <FaIcon classes={ ['user'] }/> { user.data.user_login }
        </div> }
      </div> )
    },
  },
  {
    id: 'contact-info',
    name: 'Contact Info',
    render: ({ item }) => {

      return (
        <div className={ 'contact-info' }>
          <div className={ 'email' }>
            { item.data.email && <span className={ 'email' }>
                    <a href={ 'mailto:' + item.data.email }><FaIcon
                      classes={ ['envelope-square'] }/> { item.data.email }</a>
                  </span> }
          </div>
          { item.meta.primary_phone &&
          <div className={ 'phone' }>
            <a href={ 'tel:' + item.meta.primary_phone }>
              <span className={ 'phone' }><FaIcon
                classes={ ['phone-square'] }/> { item.meta.primary_phone }</span>
              { item.meta.primary_phone_extension &&
              <span
                className={ 'ext' }> x{ item.meta.primary_phone_extension }</span> }
            </a>
          </div>
          }
        </div>
      )
    },
  },
  {
    id: 'status',
    name: 'Status',
    render: ({ item }) => {
      return optinStatusMap[item.data.optin_status]
    },
  },
  {
    id: 'date_created',
    name: 'Date Added',
    render: ({ item }) => {
      return moment(item.data.date_created).format('LLL')
    },
  },
  {
    id: 'actions',
    name: 'Actions',
    render: ({ item }) => {
      return <Button size={'sm'} variant={'outline-secondary'}>{'Email'}</Button>
    },
  },
]

const ContactsList = ({
  fetching,
  totalContacts,
  context,
  query,
  contacts,
  error,
  changeContext,
  fetchContacts,
  clearState,
  fetchMoreContacts,
  updateQuery,
  clearItems,
}) => {

  let timer = useRef(null)

  const loadMoreContacts = () => {
    updateQuery({
      offset: query.offset + query.number,
    })

    fetchMoreContacts()
  }

  const updateFilters = (queryVars) => {

    if (timer.current) {
      clearTimeout(timer.current)
    }

    updateQuery({
      ...queryVars,
      offset: 0,
    })

    timer.current = setTimeout(() => {
      fetchContacts()
      clearItems()
    }, 1000)
  }

  const handleSearch = (term) => {
    updateFilters({
      search: term,
    })
  }

  const handleStatusFilter = (stati) => {
    updateFilters({
      optin_status: stati && stati.length > 0 ? stati : null,
    })
  }

  const handleTagFilter = (tags) => {
    changeContext({
      tags_filter: tags,
    })
    updateFilters({
      tags_include: tags && tags.length > 0 ? tags.map(tag => tag.value) : null,
    })
  }

  useEffect(() => {
    fetchContacts()
    return () => {
      clearState()
      clearTimeout(timer.current)
    }
  }, [])

  return (
    <div>
      <div className={ 'contact-table-actions' }>
        <div className={ 'total-contacts' }>
          { totalContacts > 0 &&
          <span className={ 'number' }>{ number_format(totalContacts) }</span> }
        </div>
        <div className={ 'filters' }>
          <div className={ 'tag-filter filter' }>
            <TagPicker
              selectProps={ {
                placeholder: 'Filter by Tag...',
              } }
              onChange={ handleTagFilter }
              value={ context.tags_filter }
            />
          </div>
          <div className={ 'status-filter filter' }>
            <Select
              isMulti
              placeholder={ 'Filter by Status...' }
              options={ optinStatusFilters }
              onChange={ (values) => handleStatusFilter(
                values && values.map(item => item.value)) }
              value={ query.optin_status && query.optin_status.map(
                status => optinStatusFilters.find(
                  item => status === item.value)) }
            />
          </div>
          <div
            className={ 'quick-search filter' }
          >
            <Form.Control
              type={ 'search' }
              placeholder={ 'Search' }
              value={ query.search }
              onChange={ (e) => handleSearch(e.target.value) }
            />
          </div>
        </div>
      </div>
      <ListTable
        isLoading={ fetching }
        items={ contacts }
        columns={ columns }
        fetchData={ loadMoreContacts }
      />
    </div>
  )
}

const mapStateToProps = state => ( {
  query: state.contactList.query,
  contacts: state.contactList.data,
  fetching: state.contactList.fetching,
  error: state.contactList.error,
  context: state.contactList.context,
  totalContacts: state.contactList.total,
} )

const ConnectedContactsList = connect(mapStateToProps,
  {
    fetchContacts,
    fetchMoreContacts,
    updateQuery,
    resetQuery,
    clearItems,
    changeContext,
    clearState,
  })(
  ContactsList)

export default {
  path: '/contacts',
  icon: 'user',
  title: 'Contacts',
  capabilities: [],
  exact: true,
  render: () => <div className={ 'contacts' }>
    <header className={ 'with-padding' }>
      <h2>{ 'Contacts' }</h2>
      <div className={ 'page-actions' }>
        <IconButton
          icon={ 'plus-circle' }
          variant={ 'icon-only' }
        />
      </div>
    </header>
    <ConnectedContactsList/>
  </div>,
}